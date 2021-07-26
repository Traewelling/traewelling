<template>
    <div class="spinner-container">
        <div class="spinner">
            <div aria-hidden="true" class="track"></div>
            <div aria-hidden="true" class="train"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Spinner"
}
</script>

<style scoped>
.spinner {
    position: relative;
    width: 60px;
    height: 100px;
}

.spinner-container {
    margin-left: 50%;
}

.track {
    position: relative;
    overflow: hidden;
    width: 50px;
    height: 100px;
    left: -1px;
    border-left: 5px solid #333;
    transform: skew(-10deg) rotateX(45deg);
}

.track:before {
    content: "";
    position: absolute;
    height: 7px;
    width: 50px;
    background-color: #333;
    top: 90px;
    box-shadow: 0 0 #333, 0 -14px #333, 0 -28px #333, 0 -42px #333, 0 -56px #333, 0 -70px #333, 0 -84px #333, 0 -98px #333, 0 -112px #333, 0 -126px #333, 0 -140px #333;
    animation: track 1s linear infinite;
}

@keyframes track {
    0% {
        transform: translateY(70px) rotateX(45deg);
    }
    100% {
        transform: translateY(0px) rotateX(45deg);
    }
}

.track:after {
    content: "";
    position: absolute;
    transform: rotate(-15deg);
    width: 50px;
    height: 120px;
    background-color: #fff;
    border-left: 5px solid #333;
    left: 28px;
    top: -10px;
}

.train {
    position: absolute;
    width: 60px;
    height: 60px;
    background-color: #333;
    border-radius: 15px;
    top: 0;
    left: -13px;
    transform-origin: bottom;
    animation: rotate 1s linear infinite;
}

.train:before {
    content: "";
    position: absolute;
    background-color: #ced4da;
    border-radius: 15%;
    width: 42px;
    height: 20px;
    left: 9px;
    top: 12px;
}

.train:after {
    content: "";
    position: absolute;
    background-color: #ced4da;
    border-radius: 50%;
    height: 10px;
    width: 10px;
    top: 40px;
    left: 10px;
    box-shadow: 30px 0px #ced4da;
}

@keyframes rotate {
    0% {
        transform: rotate(0);
    }
    25% {
        transform: rotate(2deg);
    }
    50% {
        transform: rotate(0);
    }
    75% {
        transform: rotate(-2deg);
    }
    100% {
        transform: rotate(0);
    }
}
</style>
