<template>
  <v-container fluid v-resize="onResize" v-if="!xs">
    <v-row v-if="!store.movies.length">
      <v-col>
        <span class="text-purple-accent-1">Currently, no movies.</span>
        <!-- <v-icon size="large" class="ml-2" color="purple-accent-1" :icon="!showWishList ? 'mdi-alpha-a-box' : 'mdi-alpha-w-box'"></v-icon> -->
      </v-col>
    </v-row>
    <!-- {{movies.map(m => m.name)}} -->

    <!-- -------------- -->
    <!-- GRID OF MOVIES -->
    <!-- -------------- -->
    <!-- <v-row v-for="(row, idx) in store.moviesFiltered.movies" :key="idx"> -->
    <v-row dense>
      <!-- <v-col v-for="movie in row" cols="6" xs="6" sm="4" md="3" lg="2" xl="1" :key="movie.id"> -->
      <!-- v-for="movie in store.moviesFiltered.movies_unchunk" -->
      <v-col
        v-for="movie in movies"
        cols="6"
        xs="6"
        sm="4"
        md="3"
        lg="2"
        xl="1"
        :key="movie.id"
      >
        <v-hover v-slot="{ isHovering, props }">
          <v-card
            @click="!movie.wish_listed ? setMovie(movie) : null"
            :loading="movie.loading"
            v-if="movie.paths"
            v-bind="props"
            transition="fade-transition"
          >
            <div
              v-if="movie.quality == 2160"
              style="
                position: absolute;
                top: 3px;
                right: 3px;
                z-index: 10;
                mix-blend-mode: difference;
              "
            >
              <v-icon icon="mdi:mdi-video-4k-box"></v-icon>
            </div>
            <div
              v-if="movie.watched"
              style="
                position: absolute;
                top: 3px;
                left: 3px;
                z-index: 10;
                mix-blend-mode: difference;
              "
            >
              <v-icon icon="mdi:mdi-eye-outline"></v-icon>
            </div>
            <v-overlay
              :model-value="isHovering"
              contained
              scrim="#036358"
              class="d-flex align-center justify-center"
              v-if="movie.wish_listed"
            >
              <div>
                <v-btn
                  variant="flat"
                  icon="mdi-plus"
                  class="mr-2"
                  color="grey-lighten-2"
                  @click="addToCatalog(movie)"
                ></v-btn>
                <v-btn
                  variant="flat"
                  icon="mdi-close"
                  color="grey-darken-3"
                  @click="deleteMovie(movie)"
                ></v-btn>
              </div>
            </v-overlay>
            <v-img :src="`storage/posters/${movie.paths.poster_grid}`"></v-img>
          </v-card>
        </v-hover>
      </v-col>
    </v-row>
    <div
      style="
        width: 1px;
        height: 1px;
        margin-top: -900px;
      "
      v-intersect="onIntersect"
      v-if="movies.length && currentPage"
    ></div>
  </v-container>

  <!-- -------------- -->
  <!-- LIST OF MOVIES -->
  <!-- -------------- -->
  <!-- :items="store.moviesFiltered.movies_unchunk" -->
  <v-list v-if="xs">
    <RecycleScroller
      v-if="movies.length"
      class="scroller"
      :items="movies"
      :item-size="83"
      key-field="id"
      v-slot="{ item, index }"
    >
      <div>
        <v-list-item
          link
          style="padding: 2px 0px"
          @click="!item.wish_listed ? setMovie(item) : null"
        >
          <v-list-item-title class="mb-1">{{ item.name }}</v-list-item-title>
          <v-list-item-subtitle>
            <v-chip
              color="purple-lighten-4"
              size="x-small"
              variant="outlined"
              label
              class="mr-1"
            >
              {{ item.certification }}
            </v-chip>
            <v-chip
              color="blue-lighten-4"
              size="x-small"
              variant="outlined"
              prepend-icon="mdi-clock"
              class="mr-2"
            >
              {{ item.runtime }} mins</v-chip
            >
            <v-icon
              size="small"
              :icon="item.watched ? 'mdi-eye' : 'mdi-eye-off'"
            ></v-icon>
          </v-list-item-subtitle>
          <template v-slot:prepend>
            <v-avatar
              :key="item.id"
              :image="`storage/posters/${item.paths.poster_grid}`"
              rounded="0"
              size="79"
              style="width: 60px"
            ></v-avatar>
          </template>
          <template v-slot:append v-if="item.quality == 2160">
            <v-icon
              color="yellow-accent-3"
              icon="mdi-video-4k-box"
              class="mr-3"
            ></v-icon>
          </template>
        </v-list-item>
        <v-divider
          v-if="index < store.moviesFiltered.movies_unchunk.length - 1"
        ></v-divider>
      </div>
    <div
      style="
        width: 1px;
        height: 1px;
        margin-top: -900px;
      "
      v-intersect="onIntersect"
      v-if="movies.length && currentPage"
    ></div>
    </RecycleScroller>
  </v-list>

  <MovieActiveDialog />
</template>

<script>
import {defineAsyncComponent} from 'vue'
import Layout from "@/Pages/Layout/Layout.vue";
// import MovieActiveDialog from "@/Components/Movies/MovieActiveDialog.vue";
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useCollectionStore } from "@/Store/CollectionStore.js";
import { useDisplay } from "vuetify";
// import {useBoutiqueStore} from "@/Store/BoutiqueStore.js"

export default {
  name: "MovieIndex",
  layout: Layout,

  components: {
    MovieActiveDialog: defineAsyncComponent(() =>
      import('@/Components/Movies/MovieActiveDialog.vue')
    )
  },

  created() {
    this.getMovies();
    this.getCollections();
  },

  mounted() {
    this.onResize();
  },

  computed: {},

  data() {
    return {
      overlay: true,
    };
  },

  methods: {
    async setMovie(movie) {
      try {
        movie.loading = true;
        await this.setActiveMovie(movie);

        movie.loading = false;
        this.setActiveMovieDialog();
      } catch (error) {
        console.log(error);
      }
    },
    async deleteMovie(movie) {
      try {
        await this.deleteSelectedMovie(movie);
        this.getMovies();
      } catch (error) {
        console.log(error);
      }
    },
    onResize() {
      // console.log({height: window.innerHeight, width: window.innerWidth, name: this.name})
      let rowSize = {
        xl: 12,
        lg: 6,
        md: 4,
        sm: 3,
        xs: 2,
      }[this.name];

      this.setRowSize(rowSize);
    },
    onIntersect(isIntersecting, entries, observer) {
      // console.log({isIntersecting, entries, observer})
      if (isIntersecting) {
        this.onScrollIntersection();
      }
    },
  },

  setup() {
    const store = useMovieStore();
    const { movies, showWishList, currentPage } = storeToRefs(store);
    const {
      getMovies,
      setActiveMovie,
      setActiveMovieDialog,
      addToCatalog,
      deleteSelectedMovie,
      setRowSize,
      onScrollIntersection,
    } = store;
    const collectionStore = useCollectionStore();
    const { getCollections } = collectionStore;
    const { name, xs } = useDisplay();

    return {
      store,
      movies,
      getMovies,
      setActiveMovie,
      setActiveMovieDialog,
      getCollections,
      addToCatalog,
      deleteSelectedMovie,
      showWishList,
      name,
      setRowSize,
      xs,
      onScrollIntersection,
      currentPage,
    };
  },
};
</script>