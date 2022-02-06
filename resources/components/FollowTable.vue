<template>
    <table class="table table-striped table-hover" aria-describedby="describedBy">
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in users">
                <td>
                    <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                        <a href="http://localhost:8000/@ritter.danny">
                            <img :src="row.profilePicture" style="height: 4em;">
                        </a>
                    </div>
                </td>
                <td>{{ row.displayName }}</td>
                <td>
                    <MuteButton :user="row"></MuteButton>
                </td>
                <td>
                    <FollowButton :user="row"></FollowButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>

import MuteButton from "./MuteButton";
import FollowButton from "./FollowButton";

export default {
    name: "FollowTable",
    components: {FollowButton, MuteButton},
    props: {
        describedBy: null,
        users: null,
    },
    methods: {}
};
</script>

<style scoped>

</style>
