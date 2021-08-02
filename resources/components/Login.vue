<template>
    <form class="card-body" method="post" @submit.prevent="login">
        <h2 class="card-title">{{ i18n.get("_.user.login") }}</h2>
        <div class="d-flex flex-row align-items-center justify-content-center">
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
            <input id="email" v-model="email" class="form-control text-dark" required
                   type="email"/>
            <label class="form-label text-dark" for="email">
                {{ i18n.get("_.user.email") }}
            </label>
        </div>
        <div class="form-outline mb-4">
            <input id="password" v-model="password" class="form-control text-dark"
                   required type="password"/>
            <label class="form-label text-dark" for="password">
                {{ i18n.get("_.user.password") }}
            </label>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <button class="btn btn-white" type="submit">{{ i18n.get("_.user.login") }}</button>
            <a class="text-dark"
               href="#">{{ i18n.get("_.user.forgot-password") }}</a>
        </div>
    </form>
</template>
<script>
export default {
    data() {
        return {
            email: null,
            password: null,
            hasError: false
        };
    }, mounted() {
        //
    }, methods: {
        login() {
            // get the redirect object
            var redirect = this.$auth.redirect();
            this.$auth.login({
                data: {
                    email: this.email,
                    password: this.password
                },
                redirect: {name: "dashboard"},
                staySignedIn: true,
                fetchUser: true,
            }).then(() => {
                this.$auth.fetch();
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
