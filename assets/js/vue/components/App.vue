<template>
  <div class="app">
    <component :is="component" :config="config" />
  </div>
</template>

<script>
export default {
  name: "App",
  components: {},
  props: {
    component: {
      type: String,
      default: "",
    },
    config: {
      type: Object,
      default() {
        return {};
      },
    },
  },
};
</script>
