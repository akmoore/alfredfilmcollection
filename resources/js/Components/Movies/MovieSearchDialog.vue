<template>
  <v-dialog
    v-model="dialog"
    :fullscreen="true"
    transition="dialog-bottom-transition"
    persistent
    :scrim="false"
  >
    <template v-slot:activator="{ props }">
      <v-btn
        variant="plain"
        color="purple-accent-1"
        class="mr-2"
        v-bind="props"
        density="compact"
      >
        <v-icon v-if="!xs" icon="mdi:mdi-plus" class="mr-1"></v-icon>
        <v-icon v-else icon="mdi:mdi-movie-open-plus-outline"></v-icon>
        <span v-if="!xs">Movie</span>
      </v-btn>
    </template>

    <v-card>
      <v-toolbar
        density="compact"
        flat
        tile
        extended
        extension-height="80"
        style="position: fixed; width: 100%; z-index: 10"
      >
        <v-toolbar-title>Add Movie</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn variant="plain" @click="close" icon="mdi:mdi-close"></v-btn>
        </v-toolbar-items>

        <template v-slot:extension>
          <v-container style="margin-top: 40px">
            <v-row class="mb-2">
              <v-col cols="12" md="8" offset-md="2">
                <v-text-field
                  v-model="search"
                  :label="`Search ${searchFilms ? 'Movies' : 'People'}`"
                  density="compact"
                  clearable
                  @input="
                    searchFilms ? handleSearchMovies() : handleSearchPeople()
                  "
                  @click:clear="clearResults"
                >
                  <template v-slot:prepend-inner>
                    <v-btn
                      :color="multiSelect ? 'grey-lighten-3' : 'grey'"
                      :disabled="!search"
                      variant="plain"
                      :icon="multiSelectIcon"
                      @click="toggleMultiple"
                    ></v-btn>
                  </template>

                  <template
                    v-slot:prepend
                    v-if="multiSelect && selected.length >= 2"
                  >
                    <MovieMultiSelectDialog
                      :movies="selected"
                      @remove="removeFromSelected($event)"
                      @close="multiSelectDiaogClosed"
                      :wishListed="wish_listed"
                    />
                  </template>

                  <template v-slot:append>
                    <MovieManualAddDialog :wishListed="wish_listed" />

                    <v-tooltip :text="wishListText" location="top">
                      <template v-slot:activator="{ props }">
                        <v-switch
                          inline
                          v-model="wish_listed"
                          v-bind="props"
                          color="purple-accent-1"
                          hide-details
                          class="mt-n1"
                          true-icon="mdi-check-circle"
                        ></v-switch>
                      </template>
                    </v-tooltip>
                  </template>

                  <template v-slot:append-inner>
                    <v-btn
                      v-if="searchFilms"
                      color="purple-accent-1"
                      icon="mdi-movie-search-outline"
                      variant="plain"
                      @click="toggleSearchFilms"
                    ></v-btn>
                    <v-btn
                      v-else
                      color="purple-accent-1"
                      icon="mdi-account-search-outline"
                      variant="plain"
                      @click="toggleSearchFilms"
                    ></v-btn>
                  </template>
                </v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </template>
      </v-toolbar>

      <v-card-text style="margin-top: 125px">
        <v-container v-if="results" fluid>
          <!-- ------------- -->
          <!-- SEARCH MOVIES -->
          <!-- ------------- -->
          <v-row dense v-if="searchFilms">
            <v-col
              cols="6"
              sm="3"
              md="2"
              v-for="poster in results"
              :key="poster.id"
            >
              <v-card
                @click="
                  !multiSelect
                    ? setSearchedMovie(poster)
                    : addToSelected(poster)
                "
              >
                <div
                  style="position: absolute; top: 5px; left: 5px; z-index: 10"
                  v-if="multiSelect"
                >
                  <v-checkbox
                    theme="light"
                    :model-value="selected.map((m) => m.id).includes(poster.id)"
                  ></v-checkbox>
                </div>

                <div
                  v-if="poster.owned"
                  style="
                    position: absolute;
                    top: 5px;
                    right: 5px;
                    z-index: 10;
                    mix-blend-mode: difference;
                  "
                >
                  <v-icon size="x-large" icon="mdi:mdi-check-circle"></v-icon>
                </div>
                <v-img :src="poster.poster_path"></v-img>
              </v-card>
            </v-col>
          </v-row>

          <!-- ------------- -->
          <!-- SEARCH PEOPLE -->
          <!-- ------------- -->
          <v-row dense v-else>
            <v-col
              cols="6"
              sm="3"
              md="2"
              v-for="person in results"
              :key="person.id"
            >
              <v-card transition="fade-transition">
                <v-img
                  min-height="250"
                  :src="person.profile_path"
                  :lazy-src="person.profile_path"
                >
                  <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                      <v-progress-circular
                        indeterminate
                        color="grey-lighten-4"
                      ></v-progress-circular>
                    </div>
                  </template>
                </v-img>
                <v-toolbar density="compact" flat class="mt-n4">
                  <v-toolbar-title class="text-grey text-overline">{{
                    person.name
                  }}</v-toolbar-title>
                  <v-toolbar-items>
                    <v-btn
                      size="small"
                      color="purple-accent-1"
                      icon="mdi-eye-outline"
                      @click="handleSearchMoviesForPerson(person)"
                    ></v-btn>
                  </v-toolbar-items>
                </v-toolbar>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>

  <MovieAddDialog v-if="activeSearchedMovie" :wishListed="wish_listed" />
</template>

<script>
import {defineAsyncComponent} from 'vue';
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
// import MovieAddDialog from "./MovieAddDialog.vue";
// import MovieMultiSelectDialog from "./MovieMultiSelectDialog.vue";
// import MovieManualAddDialog from "./MovieManualAddDialog.vue";
import { chunk, debounce } from "lodash";
import { useDisplay } from "vuetify";

export default {
  name: "MovieSearchDialog",
  components: {
    MovieAddDialog: defineAsyncComponent(() =>
      import('./MovieAddDialog.vue')
    ),
    MovieMultiSelectDialog: defineAsyncComponent(() =>
      import('./MovieMultiSelectDialog.vue')
    ),
    MovieManualAddDialog: defineAsyncComponent(() =>
      import('./MovieManualAddDialog.vue')
    ),
  },

  data() {
    return {
      dialog: false,
      search: "",
      results: null,
      overlay: true,
      multiSelect: false,
      selected: [],
      wish_listed: false,
      searchFilms: true,
    };
  },

  computed: {
    multiSelectIcon() {
      if (this.multiSelect && this.selected.length < 10) {
        return {
          0: "mdi:mdi-numeric-0-box-multiple",
          1: "mdi:mdi-numeric-1-box-multiple",
          2: "mdi:mdi-numeric-2-box-multiple",
          3: "mdi:mdi-numeric-3-box-multiple",
          4: "mdi:mdi-numeric-4-box-multiple",
          5: "mdi:mdi-numeric-5-box-multiple",
          6: "mdi:mdi-numeric-6-box-multiple",
          7: "mdi:mdi-numeric-7-box-multiple",
          8: "mdi:mdi-numeric-8-box-multiple",
          9: "mdi:mdi-numeric-9-box-multiple",
        }[this.selected.length];
      }

      if (this.multiSelect && this.selected.length >= 10) {
        return "mdi:mdi-numeric-9-plus-box-multiple";
      }

      // return "mdi:mdi-checkbox-multiple-blank";
      return "mdi:mdi-filmstrip-box-multiple";
    },
    wishListText() {
      return `Add to Wish List: ${this.wish_listed ? "Yes" : "No"}`;
    },
  },

  methods: {
    close() {
      this.dialog = !this.dialog;
      this.search = "";
      this.results = null;
    },
    handleSearchMovies: debounce(async function () {
      try {
        let results = await this.searchMovies(this.search);
        this.results = results?.results
          .map((r) => {
            let url = "https://image.tmdb.org/t/p/w780";
            let moviesArray = this.movies.map((m) => parseInt(m.movie_db_id));
            let owned = moviesArray.includes(r.id);

            return {
              ...r,
              poster_id: r.poster_path,
              backdrop_id: r.backdrop_path,
              poster_path: r.poster_path ? url + r.poster_path : null,
              backdrop_path: r.backdrop_path ? url + r.backdrop_path : null,
              owned,
            };
          })
          .filter((m) => m.poster_path);
      } catch (error) {
        console.log(error);
      }
    }, 250),
    handleSearchPeople: debounce(async function () {
      try {
        let results = await this.searchPeople(this.search);
        this.results = results.results
          .filter((m) => m.profile_path)
          .map((m) => {
            return {
              ...m,
              profile_path: `https://image.tmdb.org/t/p/w300/${m.profile_path}`,
            };
          });
        // console.log(results.results);
      } catch (error) {
        console.log(error);
      }
    }, 500),
    async handleSearchMoviesForPerson(person) {
      // console.log({ person });
      this.toggleSearchFilms();
      let results = await this.searchMoviesForPerson(person);

      this.results = results
        .map((r) => {
          let url = "https://image.tmdb.org/t/p/w780";
          // let moviesArray = this.movies.map((m) => parseInt(m.movie_db_id));
          let owned = this.mdbids.includes(r.id);

          return {
            ...r,
            poster_id: r.poster_path,
            backdrop_id: r.backdrop_path,
            poster_path: r.poster_path ? url + r.poster_path : null,
            backdrop_path: r.backdrop_path ? url + r.backdrop_path : null,
            owned,
          };
        })
        .filter((m) => m.poster_path);

      this.search = ' ';
    },
    setSearchedMovie(poster) {
      this.setActiveSearchMovie(poster);
      this.setActiveSearchMovieDialog(true);
    },
    clearResults() {
      this.results = "";
    },
    toggleMultiple() {
      this.multiSelect = !this.multiSelect;
      this.selected = [];
    },
    addToSelected(poster) {
      if (this.selected.includes(poster)) {
        this.selected = this.selected.filter((p) => p.id != poster.id);
      } else {
        this.selected.push(poster);
      }
    },
    removeFromSelected(movie) {
      this.selected = this.selected.filter((m) => m.id != movie.id);
    },
    multiSelectDiaogClosed(type) {
      if (type == "submit") {
        this.selected = [];
        this.multiSelect = false;
      }
    },
    toggleSearchFilms() {
      this.search = null;
      this.results = null;
      this.searchFilms = !this.searchFilms;
    },
  },

  setup() {
    const store = useMovieStore();
    const { activeSearchedMovie, movies, mdbids } = storeToRefs(store);
    const {
      searchMovies,
      searchPeople,
      setActiveSearchMovie,
      setActiveSearchMovieDialog,
      searchMoviesForPerson,
    } = store;
    const { xs } = useDisplay();
    return {
      store,
      movies,
      searchMovies,
      searchPeople,
      setActiveSearchMovie,
      setActiveSearchMovieDialog,
      activeSearchedMovie,
      searchMoviesForPerson,
      xs,
      mdbids,
    };
  },
};
</script>