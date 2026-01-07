window.LaravelEcho = new Echo({
    broadcaster: 'pusher',
    key: window.EchoConfig.key,
    wsHost: window.EchoConfig.host,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    encrypted: false,
    enabledTransports: ['ws'],
    cluster: 'mt1',
    authEndpoint: '/broadcasting/auth',
});