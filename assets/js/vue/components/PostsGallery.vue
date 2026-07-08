<template>
  <div class="posts-gallery">
    <div class="posts-gallery__form">
      <div class="posts-gallery__filters">
        <div class="posts-gallery__select-wrap">
          <select
            id="posts-gallery-category"
            name="category"
            class="posts-gallery__select"
            v-model="selectedCategory"
            @change="filterByCategory()"
          >
            <option value="0">All categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="total === 0 && !loading" class="posts-gallery__no-results">
      Sorry, there are no posts that match this category.
    </div>

    <TransitionGroup name="posts-gallery-fade" tag="div" class="posts-gallery__grid">
      <div v-for="post in posts" :key="post.id" class="posts-gallery__item">
        <a class="posts-gallery__link" :href="post.customFields.link">
          <div class="posts-gallery__image-wrap">
            <img
              v-if="post.customFields.featured_image_src"
              :src="post.customFields.featured_image_src"
              :srcset="post.customFields.featured_image_srcset || null"
              sizes="(max-width: 480px) 100vw, (max-width: 1024px) 50vw, 33.33vw"
              :alt="post.customFields.featured_image_alt"
              loading="lazy"
            />
          </div>

          <div class="posts-gallery__content">
            <p v-if="post.customFields.categories_list.length" class="posts-gallery__category">
              {{ post.customFields.categories_list.join(", ") }}
            </p>
            <h3 class="posts-gallery__title" v-html="post.title"></h3>
            <p class="posts-gallery__date">{{ post.customFields.formatted_date }}</p>
          </div>
        </a>
      </div>
    </TransitionGroup>

    <div v-if="loading" class="posts-gallery__loading-spinner"></div>

    <div v-if="!loading && showMore" class="posts-gallery__load-more">
      <button type="button" class="button" @click="getMorePosts">Load more</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    config: {
      type: Object,
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      posts: [],
      categories: [],
      params: {
        page: 1,
        per_page: 12,
        orderby: "date",
        order: "desc",
      },
      selectedCategory: "0",
      loading: false,
      total: 0,
      totalPages: 0,
    };
  },
  mounted() {
    if (this.config && this.config.defaultCategoryID) {
      this.selectedCategory = String(this.config.defaultCategoryID);
    }

    this.getCategories();
    this.getPosts();
  },
  computed: {
    showMore() {
      return this.params.page < this.totalPages;
    },
  },
  methods: {
    async getCategories() {
      await this.axios
        .get("categories", {
          params: { per_page: 100, hide_empty: true },
        })
        .then((response) => {
          this.categories = response.data;
        });
    },

    buildParams() {
      const params = { ...this.params };

      if (this.selectedCategory !== "0") {
        params.categories = this.selectedCategory;
      }

      return params;
    },

    async getPosts() {
      this.loading = true;

      await this.axios
        .get("posts", { params: this.buildParams() })
        .then((response) => {
          this.posts = response.data.map((post) => ({
            id: post.id,
            title: post.title.rendered,
            customFields: post.custom_fields,
          }));
          this.total = parseInt(response.headers["x-wp-total"], 10);
          this.totalPages = parseInt(response.headers["x-wp-totalpages"], 10);
        });

      this.loading = false;
    },

    async getMorePosts() {
      this.params.page += 1;
      this.loading = true;

      await this.axios
        .get("posts", { params: this.buildParams() })
        .then((response) => {
          const morePosts = response.data.map((post) => ({
            id: post.id,
            title: post.title.rendered,
            customFields: post.custom_fields,
          }));
          this.posts = this.posts.concat(morePosts);
        });

      this.loading = false;
    },

    filterByCategory() {
      this.posts = [];
      this.params.page = 1;
      this.getPosts();
    },
  },
};
</script>
