<style>
    /* ---- Login de Fino de Sabor: plano, sin degradados ---- */
    .fi-simple-layout { background-color: #f3f6f7; }
    .dark .fi-simple-layout { background-color: #0d1519; }

    .fi-simple-main {
        max-width: 27rem;
        box-shadow: none;
        border: 1px solid #dde5e8;
    }
    .dark .fi-simple-main { border-color: rgba(255, 255, 255, .1); }

    .fi-simple-header-heading { font-weight: 700; letter-spacing: -.01em; }
    .fi-simple-main .fi-btn { width: 100%; padding-block: .7rem; }

    /* Panel lateral (solo en pantallas grandes) */
    .login-lateral { display: none; }

    @media (min-width: 1024px) {
        .login-lateral {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            inset: 0 auto 0 0;
            width: 42%;
            padding: 3rem;
            background-color: var(--primary-800, #164655);
            color: #fff;
        }
        .fi-simple-main-ctn { padding-left: 42%; }
        .fi-simple-main { margin-block: 0; }
        /* En escritorio la marca ya se ve en el panel lateral. */
        .fi-simple-header .fi-logo { display: none; }
    }

    .login-marca { display: flex; align-items: center; gap: .75rem; font-size: 1.125rem; font-weight: 600; }
    .login-monograma {
        display: grid; place-items: center;
        width: 2.5rem; height: 2.5rem;
        border-radius: .5rem;
        background-color: #fff;
        color: var(--primary-800, #164655);
        font-weight: 700; letter-spacing: .02em;
    }
    .login-mensaje h2 { font-size: 1.875rem; line-height: 1.2; font-weight: 700; max-width: 22rem; }
    .login-mensaje p { margin-top: 1rem; max-width: 22rem; color: rgba(255, 255, 255, .75); }
    .login-mensaje ul { margin-top: 2rem; max-width: 22rem; }
    .login-mensaje li {
        padding-block: .75rem;
        border-top: 1px solid rgba(255, 255, 255, .18);
        color: rgba(255, 255, 255, .9);
        font-size: .9375rem;
    }
    .login-lateral small { color: rgba(255, 255, 255, .6); }
</style>
