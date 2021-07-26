<template>
    <div>

        <div class="videoContainer">
            <div class="overlay"></div>
            <video autoplay class="fullscreen-bg__video" loop muted>
                <source src="img/vid2.mp4" type="video/mp4">
            </video>
        </div>
        <div class="d-flex w-100 h-100 mx-auto flex-column position-absolute text-white">
            <main class="d-flex mx-auto mt-auto p-4 p-md-0 p-sm-0">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-6 mb-3">
                            <div class="mb-3 align-items-center">
                                <img alt="logo" class="logo" src="/images/icons/logo.svg">
                                <span class="h1">#Träwelling</span>
                            </div>
                            <p>{{ i18n.get("_.about.block1") }}</p>
                            <a class="btn btn-white" href="#!">{{ i18n.get("_.menu.about") }}</a>
                        </div>
                        <div class="col-md-4 card text-dark">
                            <form action="#!" class="card-body">
                                <h2 class="card-title">{{ i18n.get("_.user.login") }}</h2>
                                <div class="d-flex flex-row align-items-center justify-content-center">
                                    <!--                                        <p class="lead fw-normal mb-0 me-3">Sign in with</p>-->
                                    <button aria-label="Twitter" class="btn btn-primary btn-floating mx-1"
                                            type="button"> <!--ToDo i18n?-->
                                        <i aria-hidden="true" class="fab fa-twitter"></i>
                                    </button>
                                    <button aria-label="Apple" class="btn btn-primary btn-floating mx-1"
                                            type="button"> <!--ToDo i18n?-->
                                        <i aria-hidden="true" class="fab fa-apple"></i>
                                    </button>
                                    <button aria-label="Mastodon" class="btn btn-primary btn-floating mx-1"
                                            type="button"> <!--ToDo i18n?-->
                                        <i aria-hidden="true" class="fab fa-mastodon"></i>
                                    </button>
                                </div>
                                <div class="divider d-flex align-items-center mb-4 mt-2">
                                    <p class="text-center fw-bold mx-3 mb-0">Or</p><!--ToDo i18n-->
                                </div>
                                <div class="form-outline mb-4">
                                    <input id="mail" class="form-control text-dark" type="text"/>
                                    <label class="form-label text-dark" for="mail">{{
                                            i18n.get("_.user.email")
                                        }}</label>
                                </div>
                                <div class="form-outline mb-4">
                                    <input id="password" class="form-control text-dark" type="password"/>
                                    <label class="form-label text-dark"
                                           for="password">{{ i18n.get("_.user.password") }}</label>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <input id="login" class="btn btn-white" name="login" type="button"
                                           value="Login">
                                    <a class="text-dark"
                                       href="#">{{ i18n.get("_.user.forgot-password") }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>

            <FooterComponent class="w-100" dashboard="true"></FooterComponent>
        </div>
    </div>
</template>

<script>
import FooterComponent from "../layouts/FooterComponent";

export default {
    name: "LoginView",
    components: {FooterComponent}
}
</script>
<style>
html, body {
    height: 100%;
}

#app {
    height: 100%;
}
</style>
<style scoped>
.divider:after,
.divider:before {
    content: "";
    flex: 1;
    height: 1px;
    background: #eee;
}

.logo {
    height: 50px;
}

.videoContainer {
    position: fixed;
    width: 100%;
    height: 100%;
    background-attachment: scroll;
    overflow: hidden;
}

.videoContainer video {
    min-width: 100%;
    min-height: 100%;
    position: relative;
    z-index: 1;
    background-image: url("https://images.pexels.com/photos/5387999/pexels-photo-5387999.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
    filter: grayscale(100%); /* Standard CSS */
    -webkit-filter: grayscale(100%); /* CSS for Webkit Browsers */
    filter: url(/elements/grayscale.svg#desaturate); /* Firefox 4-34 */
    filter: gray; /* Internet Explorer IE6-9 */
    -webkit-filter: grayscale(1); /* Old WebKit Browsers */
}

.videoContainer .overlay {
    height: 100%;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 2;
    background-color: #a20b11;
    opacity: 0.8;
}

</style>
