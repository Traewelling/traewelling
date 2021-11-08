<template>
    <form class="card-body" method="post" @submit.prevent="authenticate">
        <h2 class="card-title">{{ i18n.get("_.user.login") }}</h2>
        <div class="d-flex flex-row align-items-center justify-content-center">
            <button aria-label="Twitter" class="btn btn-primary btn-floating mx-1" type="button"> <!--ToDo i18n?-->
                <i aria-hidden="true" class="fab fa-twitter"></i>
            </button>
            <button aria-label="Apple" class="btn btn-primary btn-floating mx-1" type="button"> <!--ToDo i18n?-->
                <i aria-hidden="true" class="fab fa-apple"></i>
            </button>
            <button aria-label="Mastodon" class="btn btn-primary btn-floating mx-1" type="button"> <!--ToDo i18n?-->
                <i aria-hidden="true" class="fab fa-mastodon"></i>
            </button>
        </div>
        <div class="divider d-flex align-items-center mb-4 mt-2">
            <p class="text-center fw-bold mx-3 mb-0">Or</p><!--ToDo i18n-->
        </div>
        <div class="form-outline mb-4">
            <input id="login" v-model="login" autocapitalize="none" autocomplete="username"
                   autofocus="autofocus" class="form-control text-dark" required type="text"/>
            <label class="form-label text-dark" for="login">
                {{ i18n.get("_.user.login-credentials") }}
            </label>
        </div>
        <div class="form-outline mb-4">
            <input id="password" v-model="password" class="form-control text-dark" required type="password"/>
            <label class="form-label text-dark" for="password"> {{ i18n.get("_.user.password") }} </label>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <button class="btn btn-white" type="submit">{{ i18n.get("_.user.login") }}</button>
            <a class="text-dark" href="#">{{ i18n.get("_.user.forgot-password") }}</a>
        </div>
    </form>
</template>
<script>
export default {
    name: "Login",
    inject: ["notyf"],
    data() {
        return {
            login: null,
            password: null,
            hasError: false
        };
    }, mounted() {
        //
    }, methods: {
        authenticate() {
            // get the redirect object
            let redirect = this.$auth.redirect();
            this.$auth.login({
                data: {
                    login: this.login,
                    password: this.password
                },
                redirect: {name: redirect ? redirect.from.name : "dashboard"},
                staySignedIn: true,
                fetchUser: true,
            }).then(() => {
                this.$auth.fetch();
            }).catch((error) => {
                this.notyf.error(this.i18n.get("_.messages.exception.general"));
            });
        }
    }
};
</script>
<style scoped>
.divider:after,
.divider:before {
    content: "";
    flex: 1;
    height: 1px;
    background: #eee;
}
</style>
