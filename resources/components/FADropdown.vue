<template>
  <div class="btn-group">
    <button class="btn btn-sm dropdown-toggle" :class="buttonClass"
            type="button"
            ref="dropdownButton"
            data-mdb-toggle="dropdown"
            aria-expanded="false"
            :aria-label="dropDown[selected].desc"
    >
      <i :class="dropDown[selected].icon" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu" :aria-labelledby="$refs.dropdownButton">
      <li v-for="(item, key) in dropDown" class="dropdown-item" @click="selectItem(key)">
        <i :class="item.icon" aria-hidden="true"></i> {{ i18n.get(item.desc) }}
        <span v-if="item.detail" class="text-muted"><br/>{{ i18n.get(item.detail) }}</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "FADropdown",
  data() {
    return {
      m_selected: null
    };
  },
  props: {
    btnClass: null,
    dropdownContent: null,
    preSelect: null
  },
  computed: {
    buttonClass() {
      if (this.$props.btnClass) {
        return this.$props.btnClass;
      }
      return "btn-outline-primary";
    },
    dropDown() {
      return this.$props.dropdownContent;
    },
    selected() {
      if (!this.m_selected && this.$props.preSelect) {
        return this.$props.preSelect;
      }
      if (this.m_selected) {
        return this.m_selected;
      }
      return 0;
    }
  },
  methods: {
    selectItem(key) {
      this.m_selected = key;
    }
  }
}
</script>

<style scoped>

</style>