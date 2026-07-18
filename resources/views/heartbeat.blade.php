<script>
    (function () {
        var endpoint = @json($endpoint);
        var interval = {{ (int) $interval * 1000 }};
        var token = document.querySelector('meta[name="csrf-token"]');

        function ping() {
            if (document.hidden) {
                return;
            }

            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                }
            }).catch(function () {});
        }

        ping();
        setInterval(ping, interval);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                ping();
            }
        });
    })();
</script>
