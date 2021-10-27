<template>
    <router-view></router-view>
</template>

<script>
import LayoutBasic from "./layouts/Basic";

export default {
    name: "App",
    inject: ["notyf"],
    metaInfo() {
        return {
            title: "Träwelling",
            titleTemplate: "%s - Träwelling",//ToDo get name from .env
            htmlAttrs: {
                lang: this.i18n.getLocale()
            },
            meta: [
                {name: "charset", "content": "utf-8"},
                {name: "viewport", content: "width=device-width, initial-scale=1"},
                {name: "apple-mobile-web-app-capable", content: "yes"},
                {name: "apple-mobile-web-app-status-bar-style", content: "#c72730"},
                {name: "mobile-web-app-capable", content: "yes"},
                {name: "theme-color", content: "#c72730"},
                {name: "name", content: "Träwelling"}, //ToDo get name from .env

                {name: "copyright", content: "Träwelling Team"},
                {name: "description", content: this.i18n.get("_.about.block1"), vmid: "description"},
                {
                    name: "keywords",
                    content: "Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon"
                },
                {name: "audience", conent: "Travellers"},
                {name: "DC.Rights", content: "Träwelling Team"},
                {name: "DC.Description", content: this.i18n.get("_.about.block1"), vmid: "DC.Description"},
                {name: "DC.Language", content: this.i18n.getLocale()},
                {property: "og:title", content: "Träwelling", vmid: "og:title"}, //ToDo get name from .env
                {property: "og:site_name", content: "Träwelling"}, //ToDo get name from .env
                {property: "og:type", content: "website"},
                {name: "robots", content: "index,follow", vmid: "robots"}
            ]
        };
    },
    components: {
        LayoutBasic
    },
    mounted() {
        this.$auth.load().then(() => {
            if (this.$auth.check()) {
                this.$auth.fetch()
                    .then((res) => {
                        this.$auth.user(res.data.data);
                        if (this.$auth.user().language) {
                            this.setLang(this.$auth.user().language);
                        }
                    });
            }
        });
    },
    watch: {
        '$route'() {
            $("#navbarCollapse").collapse("hide");
        }
    }
};
</script>
