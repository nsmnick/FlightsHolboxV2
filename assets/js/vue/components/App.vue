<template>
  <div class="app">
    <component :is="component" :config="config" />
  </div>
</template>

<script>
import PostsGallery from "./PostsGallery.vue";

export default {
  name: "App",
  components: {
    PostsGallery,
  },
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
