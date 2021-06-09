<template>
  <div>
    <main class="py-4">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
export default {
  mounted() {
    this.$auth.load().then(() => {
      if (this.$auth.check()) {
        this.$auth.fetch()
            .then((res) => {
              this.$auth.user(res.data);
            });
      }
    });
  }
};
</script>
