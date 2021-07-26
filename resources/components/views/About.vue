<template>
    <LayoutBasic>
        <div class="row justify-content-center">
            <div id="about-page" class="col-lg-8">
                <h1>{{ i18n.get("_.about.heading") }}</h1>
                <blockquote class="blockquote"><p>{{ i18n.get("_.about.block1") }}</p></blockquote>

                <h2>F.A.Q. <small class="text-muted">{{ i18n.get("_.about.faq-heading") }}</small></h2>

                <h3>{{ i18n.get("_.about.who-heading") }}</h3>
                <p class="lead">{{ i18n.get("_.about.who") }} <a href="url('humans.txt')">humans.txt</a></p>

                <h3>{{ i18n.get("_.about.name-heading") }}</h3>
                <p class="lead" v-html="i18n.get('_.about.name')"></p>

                <h3>{{ i18n.get("_.about.no-train-heading") }}</h3>
                <p class="lead">{{ i18n.get("_.about.no-train") }}</p>

                <h3>{{ i18n.get("_.about.feature-missing-heading") }}</h3>
                <p class="lead">{{ i18n.get("_.about.feature-missing") }} <a
                    href="mailto:hi@traewelling.de">hi@traewelling.de</a>.</p>

                <h3>{{ i18n.get("_.about.points-heading") }}</h3>
                <p class="lead">
                    {{ i18n.get("_.about.points1") }}
                </p>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">{{ i18n.get("_.about.productclass") }}</th>
                            <th scope="col">{{ i18n.get("_.about.basepoints") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">{{ i18n.get("_.about.tram") }}</th>
                            <td>2</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ i18n.get("_.about.suburban") }}</th>
                            <td>3</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ i18n.get("_.about.regional") }}</th>
                            <td>5</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ i18n.get("_.about.express") }}</th>
                            <td>10</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ i18n.get("_.about.international") }}</th>
                            <td>10</td>
                        </tr>
                    </tbody>
                </table>

                <p class="lead" v-html="i18n.get('_.about.calculation')"></p>

            </div>
        </div>
    </LayoutBasic>
</template>

<script>
import LayoutBasic from "../layouts/Basic";

export default {
    name: "About",
    components: {LayoutBasic},
    metaInfo() {
        return {
            title: this.i18n.get("_.about.faq-heading"),
            meta: [
                {name: "robots", content: "index", vmid: "robots"}
            ]
        };
    }
};
</script>

<style scoped>

</style>
