// Passkeys (WebAuthn) — helpers de conversão base64url <-> ArrayBuffer e os
// fluxos de registro e login por asserção, consumindo os endpoints do backend.
//
// Não há criptografia aqui: a WebAuthn API do navegador
// (navigator.credentials.create/get) faz o trabalho; estas funções apenas
// convertem os campos base64url e conversam com o servidor via fetch (sessão +
// CSRF). Erros do autenticador (cancelamento/expiração) viram mensagens amigáveis.

function base64urlParaBuffer(base64url) {
    const padding = "=".repeat((4 - (base64url.length % 4)) % 4);
    const base64 = (base64url + padding).replace(/-/g, "+").replace(/_/g, "/");
    const binario = atob(base64);
    const bytes = new Uint8Array(binario.length);
    for (let i = 0; i < binario.length; i++) {
        bytes[i] = binario.charCodeAt(i);
    }
    return bytes.buffer;
}

function bufferParaBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binario = "";
    for (const b of bytes) {
        binario += String.fromCharCode(b);
    }
    return btoa(binario)
        .replace(/\+/g, "-")
        .replace(/\//g, "_")
        .replace(/=+$/, "");
}

function tokenCsrf() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? ""
    );
}

async function postar(url, corpo) {
    return fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": tokenCsrf(),
            "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
        body: JSON.stringify(corpo ?? {}),
    });
}

// Extrai a primeira mensagem de erro de validação de uma resposta 422.
async function mensagemDeErro(resp, fallback) {
    try {
        const dados = await resp.json();
        if (dados?.errors) {
            const primeira = Object.values(dados.errors)[0];
            if (Array.isArray(primeira) && primeira.length) return primeira[0];
        }
        if (typeof dados?.message === "string" && dados.message) {
            return dados.message;
        }
    } catch (_) {
        /* corpo não-JSON: usa o fallback */
    }
    return fallback;
}

// Traduz erros da WebAuthn API em mensagens amigáveis, sem disparar dialogs.
function traduzErroAutenticador(erro) {
    if (erro instanceof DOMException) {
        if (erro.name === "NotAllowedError") {
            return "Operação cancelada ou expirada. Tente novamente.";
        }
        if (erro.name === "InvalidStateError") {
            return "Este dispositivo já possui uma passkey cadastrada nesta conta.";
        }
        if (erro.name === "AbortError") {
            return "Operação interrompida. Tente novamente.";
        }
        if (erro.name === "SecurityError") {
            return "Ambiente inseguro para passkeys. Use HTTPS e o domínio correto.";
        }
    }
    return erro?.message || "Não foi possível concluir a operação com a passkey.";
}

const Passkeys = {
    /** O navegador suporta WebAuthn? */
    suportado() {
        return (
            typeof window.PublicKeyCredential !== "undefined" &&
            typeof navigator.credentials?.create === "function"
        );
    },

    /**
     * Registra uma nova passkey: pede options, cria a credencial e a persiste.
     * Retorna o objeto da passkey criada; lança Error com mensagem amigável.
     */
    async registrar({ optionsUrl, storeUrl, nome }) {
        if (!this.suportado()) {
            throw new Error("Este navegador não suporta passkeys.");
        }

        const respOpcoes = await postar(optionsUrl);
        if (!respOpcoes.ok) {
            throw new Error(
                await mensagemDeErro(
                    respOpcoes,
                    "Não foi possível iniciar o cadastro da passkey."
                )
            );
        }

        const { publicKey } = await respOpcoes.json();
        publicKey.challenge = base64urlParaBuffer(publicKey.challenge);
        publicKey.user.id = base64urlParaBuffer(publicKey.user.id);
        if (Array.isArray(publicKey.excludeCredentials)) {
            publicKey.excludeCredentials = publicKey.excludeCredentials.map(
                (c) => ({ ...c, id: base64urlParaBuffer(c.id) })
            );
        }

        let credencial;
        try {
            credencial = await navigator.credentials.create({ publicKey });
        } catch (erro) {
            throw new Error(traduzErroAutenticador(erro));
        }

        const payload = {
            id: credencial.id,
            rawId: bufferParaBase64url(credencial.rawId),
            type: credencial.type,
            response: {
                clientDataJSON: bufferParaBase64url(
                    credencial.response.clientDataJSON
                ),
                attestationObject: bufferParaBase64url(
                    credencial.response.attestationObject
                ),
            },
            name: nome || "Passkey",
        };

        const respSalvar = await postar(storeUrl, payload);
        if (!respSalvar.ok) {
            throw new Error(
                await mensagemDeErro(
                    respSalvar,
                    "Não foi possível validar a passkey. Tente novamente."
                )
            );
        }

        const { passkey } = await respSalvar.json();
        return passkey;
    },

    /**
     * Autentica por passkey (login sem senha). Pede options para o e-mail,
     * obtém a asserção e verifica no servidor. Retorna a URL de redirecionamento.
     */
    async autenticar({ optionsUrl, loginUrl, email, remember }) {
        if (!this.suportado()) {
            throw new Error("Este navegador não suporta passkeys.");
        }

        const respOpcoes = await postar(optionsUrl, { email });
        if (!respOpcoes.ok) {
            throw new Error(
                await mensagemDeErro(
                    respOpcoes,
                    "Não foi possível entrar com passkey."
                )
            );
        }

        const { publicKey } = await respOpcoes.json();
        publicKey.challenge = base64urlParaBuffer(publicKey.challenge);
        if (Array.isArray(publicKey.allowCredentials)) {
            publicKey.allowCredentials = publicKey.allowCredentials.map((c) => ({
                ...c,
                id: base64urlParaBuffer(c.id),
            }));
        }

        let asercao;
        try {
            asercao = await navigator.credentials.get({ publicKey });
        } catch (erro) {
            throw new Error(traduzErroAutenticador(erro));
        }

        const resposta = {
            clientDataJSON: bufferParaBase64url(asercao.response.clientDataJSON),
            authenticatorData: bufferParaBase64url(
                asercao.response.authenticatorData
            ),
            signature: bufferParaBase64url(asercao.response.signature),
        };
        if (asercao.response.userHandle) {
            resposta.userHandle = bufferParaBase64url(
                asercao.response.userHandle
            );
        }

        const payload = {
            email,
            id: asercao.id,
            rawId: bufferParaBase64url(asercao.rawId),
            type: asercao.type,
            response: resposta,
            remember: Boolean(remember),
        };

        const respLogin = await postar(loginUrl, payload);
        if (!respLogin.ok) {
            throw new Error(
                await mensagemDeErro(
                    respLogin,
                    "E-mail ou passkey inválidos."
                )
            );
        }

        const { redirect } = await respLogin.json();
        return redirect;
    },

    /** Dispensa o banner de convite de passkey (persistido por usuário). */
    async dispensarBanner(url) {
        try {
            await postar(url);
        } catch (_) {
            /* dispensa é best-effort: falha de rede não deve travar a UI */
        }
    },
};

window.Passkeys = Passkeys;

export default Passkeys;
