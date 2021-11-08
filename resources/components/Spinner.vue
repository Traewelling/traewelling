<template>
    <div class="row my-5">
        <div class="spinner-container">
            <div class="spinner">
                <i v-for="icon in loadingIcons" v-bind:key="icon" :class="icon" aria-hidden="true" class="fas"></i>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Spinner",
    data() {
        return {
            icons: [
                "fa-taxi",
                "fa-bus-alt",
                "fa-subway",
                "fa-tram",
                "fa-bicycle",
                "fa-ship"],
            funIcons: [
                "fa-space-shuttle",
                "fa-walking",
                "fa-car-crash",
                "fa-fighter-jet",
                "fa-ambulance",
                "fa-tractor",
                "fa-rocket",
                "fa-paper-plane",
                "fa-baby-carriage",
                "fa-sleigh"
            ]
        };
    },
    computed: {
        loadingIcons() {
            let availableIcons = this.shuffle(this.icons);
            //Randomly add fun icon
            if (Math.random() < 0.1) {
                availableIcons = this.shuffle(this.funIcons);
            }

            return availableIcons.slice(0, 6);
        }
    },
    methods: {
        shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j    = Math.floor(Math.random() * (i + 1));
                const temp = array[i];
                array[i]   = array[j];
                array[j]   = temp;
            }
            return array;
        }
    }
};
</script>

<style scoped>
.spinner-container {
    font-size: 8em;
    position: relative;
    height: 1em;
}

@keyframes Burst {
    0% {
        opacity: 0;
    }
    5% {
        opacity: 0.6;
    }
    20% {
        opacity: 0;
        transform: scale(1.8);
    }
    60% {
        opacity: 0;
    }
    100% {
        opacity: 0;
    }
}

.spinner i {
    text-align: center;
    animation: Burst 6s infinite linear;
    animation-fill-mode: both;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}

.spinner i:nth-child(1) {
    animation-delay: 1s
}

.spinner i:nth-child(2) {
    animation-delay: 2s
}

.spinner i:nth-child(3) {
    animation-delay: 3s
}

.spinner i:nth-child(4) {
    animation-delay: 4s
}

.spinner i:nth-child(5) {
    animation-delay: 5s
}

.spinner i:nth-child(6) {
    animation-delay: 6s
}
</style>
