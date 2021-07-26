<template>
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto">
            <div>
                <h3 class="float-md-start mb-0">Cover</h3>
                <nav class="nav nav-masthead justify-content-center float-md-end">
                    <a aria-current="page" class="nav-link active" href="#">Home</a>
                    <a class="nav-link" href="#">Features</a>
                    <a class="nav-link" href="#">Contact</a>
                </nav>
            </div>
        </header>

        <main class="px-3">
            <h1>Cover your page.</h1>
            <p class="lead">Cover is a one-page template for building simple and beautiful home pages. Download, edit
                the text, and add your own fullscreen background photo to make it your own.</p>
            <p class="lead">
                <a class="btn btn-lg btn-secondary fw-bold border-white bg-white" href="#">Learn more</a>
            </p>
        </main>

        <footer class="mt-auto text-white-50">
            <p>Cover template for <a class="text-white" href="https://getbootstrap.com/">Bootstrap</a>, by <a
                class="text-white" href="https://twitter.com/mdo">@mdo</a>.</p>
        </footer>
    </div>
</template>

<script>
export default {
    name: "LoginView"
}
</script>

<style scoped>
/*
 * Globals
 */


/* Custom default button */
.btn-secondary,
.btn-secondary:hover,
.btn-secondary:focus {
    color: #333;
    text-shadow: none; /* Prevent inheritance from `body` */
}


/*
 * Base structure
 */

body {
    text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
    box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
}

.cover-container {
    max-width: 42em;
}


/*
 * Header
 */

.nav-masthead .nav-link {
    padding: .25rem 0;
    font-weight: 700;
    color: rgba(255, 255, 255, .5);
    background-color: transparent;
    border-bottom: .25rem solid transparent;
}

.nav-masthead .nav-link:hover,
.nav-masthead .nav-link:focus {
    border-bottom-color: rgba(255, 255, 255, .25);
}

.nav-masthead .nav-link + .nav-link {
    margin-left: 1rem;
}

.nav-masthead .active {
    color: #fff;
    border-bottom-color: #fff;
}

</style>
