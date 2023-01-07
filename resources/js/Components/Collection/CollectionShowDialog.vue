<template>
  <div v-if="!collection">N/A</div>
  <v-dialog
    v-else
    v-model="dialog"
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
    width="900"
    scrollable
  >
    <template v-slot:activator="{ props }">
      <v-btn variant="text" density="compact" v-bind="props">
        <v-icon
          color="purple-lighten-3"
          icon="mdi:mdi-filmstrip-box-multiple"
          class="mr-2"
        ></v-icon>
        {{ collection.name }}
      </v-btn>
    </template>

    <v-card>
      <v-toolbar flat tile density="compact">
        <v-toolbar-title>{{ collection.name }}</v-toolbar-title>
        <v-toolbar-items>
          <v-btn-toggle
            v-model="filter"
            rounded="0"
            color="purple-accent-1 text-purple-darken-4"
            group
          >
            <v-btn value="alpha">
              <v-icon start> mdi-sort-alphabetical-ascending </v-icon>
              <span class="hidden-sm-and-down">Alpha</span>
            </v-btn>
            <v-btn value="chrono">
              <v-icon start> mdi-sort-calendar-ascending </v-icon>
              <span class="hidden-sm-and-down">Chrono</span>
            </v-btn>
          </v-btn-toggle>
          <v-btn @click="dialog = false" icon="mdi:mdi-close"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text style="height: 500px">
        <v-row>
          <v-col
            cols="6"
            sm="3"
            v-for="movie in filteredMovies"
            :key="movie.id"
          >
            <v-card @click="setMovie(movie)" :loading="movie.loading">
              <v-img :src="`storage/posters/${movie.paths.poster_grid}`"></v-img>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
// import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useDisplay } from "vuetify";

export default {
  name: "CollectionShowDialog",

  props: {
    collection: Object,
  },

  data() {
    return {
      dialog: false,
      filter: null,
    };
  },

  computed: {
    filteredMovies() {
      if (!this.filter) {
        return this.collection.movies.sort((a, b) => (a.id > b.id ? 1 : -1));
      }

      if (this.filter == "alpha") {
        return this.collection.movies.sort((a, b) =>
          a.sortable_name > b.sortable_name ? 1 : -1
        );
      }

      if (this.filter == "chrono") {
        return this.collection.movies
          .map((m) => {
            let [year, month, day] = m.release_date.split("-");
            return { ...m, sortable_date: `${month}${day}${year}` };
          })
          .sort((a, b) => (a.release_date > b.release_date ? 1 : -1));
      }
    },
  },

  methods: {
    async setMovie(movie) {
      try {
        await this.setActiveMovie(movie);
      } catch (error) {
        console.log(error);
      }
    },
  },

  setup() {
    const store = useMovieStore();
    const { setActiveMovie } = store;
    const { xs } = useDisplay();

    return {
      setActiveMovie,
      xs,
    };
  },
};
</script>