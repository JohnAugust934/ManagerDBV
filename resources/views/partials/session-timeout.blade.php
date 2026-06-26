{{--
    Expiração automática de sessão por inatividade.

    O Laravel encerra a sessão após SESSION_LIFETIME minutos sem requisições
    (janela deslizante: cada requisição renova o prazo). Sem este script, o
    usuário só percebe a expiração ao tentar uma ação — recebendo um erro 419.

    Aqui, ao atingir o tempo de inatividade, redirecionamos automaticamente
    para a tela de login com um aviso. O timer NÃO é renovado por mera atividade
    do mouse/teclado (que não renova a sessão no servidor), apenas por
    requisições reais: carregamento de página e chamadas fetch ao próprio host.
--}}
@auth
    <script>
        (function () {
            // Espelha config('session.lifetime'); +15s de folga para garantir que
            // o servidor já tenha de fato expirado a sessão antes de redirecionar.
            var LIFETIME_MS = {{ (int) config('session.lifetime') }} * 60 * 1000;
            if (!LIFETIME_MS) return;
            var GRACE_MS = 15 * 1000;
            var LOGIN_URL = @json(route('login') . '?expired=1');

            var timer = null;

            function expirar() {
                window.location.href = LOGIN_URL;
            }

            function reiniciar() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(expirar, LIFETIME_MS + GRACE_MS);
            }

            // Cada requisição ao próprio servidor renova a sessão no backend,
            // então também renova o cronômetro do cliente.
            var origFetch = window.fetch;
            if (origFetch) {
                window.fetch = function () {
                    try {
                        var arg = arguments[0];
                        var url = (arg && arg.url) ? arg.url : String(arg || '');
                        var mesmaOrigem = url.indexOf('://') === -1 || url.indexOf(window.location.origin) === 0;
                        if (mesmaOrigem) reiniciar();
                    } catch (e) { /* não bloquear a requisição por causa do timer */ }
                    return origFetch.apply(this, arguments);
                };
            }

            window.addEventListener('pageshow', reiniciar);
            reiniciar();
        })();
    </script>
@endauth
