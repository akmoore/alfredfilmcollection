<template>
  <v-dialog
    fullscreen
    v-model="creditMovieDialog"
    scrim="rgba(0,0,0,0.8)"
    transition="dialog-bottom-transition"
  >
    <v-card v-if="activeCredit">
      <v-toolbar style="position: fixed; width: 100%; z-index: 2">
        <v-toolbar-title>
          <v-avatar class="mr-1" v-if="smAndDown" color="grey-lighten-1">
            <v-img
              v-if="activeCredit.profile_pic"
              :src="`storage/credits/${activeCredit.profile_pic}`"
              cover
            ></v-img>
            <v-icon color="grey-darken-1" v-else icon="mdi-account"></v-icon>
          </v-avatar>
          {{ activeCredit.name }}
        </v-toolbar-title>
        <v-toolbar-items class="d-flex align-center">
          <v-btn-toggle
            v-model="filter"
            rounded="0"
            color="purple-accent-1 text-purple-darken-4"
            group
            class="mr-4"
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
          <v-btn @click="close">
            <v-icon icon="mdi:mdi-close"></v-icon>
          </v-btn>
        </v-toolbar-items>
        <template v-slot:extension>
          <v-tabs v-model="tabs" fixed-tabs centered>
            <v-tab value="catalog"> Catalog </v-tab>
            <v-tab value="all"> All </v-tab>
          </v-tabs>
        </template>
      </v-toolbar>
      <v-card-text style="margin-top: 7em">
        <v-container fluid>
          <v-row>
            <v-col cols="12" md="2" v-if="!smAndDown">
              <v-card variant="tonal">
                <v-img
                  v-if="activeCredit.profile_pic"
                  :src="`storage/credits/${activeCredit.profile_pic}`"
                ></v-img>

                <v-list>
                  <v-list-item>
                    <template v-slot:prepend v-if="!activeCredit.profile_pic">
                      <v-avatar class="" color="grey-lighten-1">
                        <v-icon
                          color="grey-darken-1"
                          icon="mdi-account"
                        ></v-icon>
                      </v-avatar>
                    </template>
                    <v-list-item-title
                      style="font-size: 20px"
                      v-text="activeCredit.name"
                    ></v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>
            <v-col cols="12" md="10">
              <v-row>
                <v-col>
                  <v-divider class="ma-2"></v-divider>
                  <div class="d-flex justify-space-between align-center">
                    <span class="ml-2 text-purple-accent-1"
                      >{{ tabs.toUpperCase() }} FILMS</span
                    >
                    <div>
                      <v-switch
                        v-if="user && tabs == 'all'"
                        v-model="wish_listed"
                        label="Wish List"
                        color="purple-accent-1"
                        hide-details
                      ></v-switch>
                    </div>
                  </div>
                  <v-divider class="ma-2"></v-divider>
                </v-col>
              </v-row>
              <v-window v-model="tabs">
                <v-window-item value="catalog">
                  <v-row dense>
                    <v-col
                      v-for="movie in filteredMovies"
                      cols="6"
                      sm="4"
                      md="3"
                      lg="2"
                      :key="movie.id"
                    >
                      <v-card @click="setMovie(movie)" :loading="movie.loading">
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
                        <v-img
                          :src="`storage/posters/${movie?.paths.poster_grid}`"
                        ></v-img>
                      </v-card>
                    </v-col>
                  </v-row>
                </v-window-item>

                <v-window-item value="all" v-if="allMovies.length">
                  <v-row dense>
                    <v-col
                      v-for="movie in filteredAllMovies"
                      cols="6"
                      sm="4"
                      md="3"
                      lg="2"
                      :key="movie.id"
                    >
                      <!-- ---------------- -->
                      <!-- CLICKABLE POSTER -->
                      <!-- ---------------- -->
                      <v-card
                        v-if="user"
                        @click="user ? setSearchedMovie(movie) : null"
                      >
                        <div
                          v-if="movie.owned"
                          style="
                            position: absolute;
                            top: 5px;
                            right: 5px;
                            z-index: 10;
                            mix-blend-mode: difference;
                          "
                        >
                          <v-icon
                            size="x-large"
                            icon="mdi:mdi-check-circle"
                          ></v-icon>
                        </div>
                        <v-img :src="`storage/${movie?.poster_path}`"></v-img>
                      </v-card>

                      <!-- -------------------- -->
                      <!-- NON-CLICKALBE POSTER -->
                      <!-- -------------------- -->
                      <v-card v-else>
                        <div
                          v-if="movie.owned"
                          style="
                            position: absolute;
                            top: 5px;
                            right: 5px;
                            z-index: 10;
                            mix-blend-mode: difference;
                          "
                        >
                          <v-icon
                            size="x-large"
                            icon="mdi:mdi-check-circle"
                          ></v-icon>
                        </div>
                        <v-img :src="`storage/${movie?.poster_path}`"></v-img>
                      </v-card>
                    </v-col>
                  </v-row>
                </v-window-item>
              </v-window>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>

    <MovieAddDialog v-if="activeSearchedMovie" :wishListed="wish_listed" />
  </v-dialog>
</template>

<script>
import { storeToRefs } from "pinia";
import { useDisplay } from "vuetify";
import MovieAddDialog from "./MovieAddDialog.vue";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useAuthStore } from "@/Store/AuthStore.js";

export default {
  name: "MovieCreditsMoviesDialog",

  components: {
    MovieAddDialog,
  },

  data() {
    return {
      filter: null,
      tabs: "catalog",
      allMovies: [],
      wish_listed: false,
    };
  },

  watch: {
    activeCredit(newVal) {
      this.getAllMovies();
    },
  },

  computed: {
    uniqueMovies() {
      return _.uniqBy(this.activeCredit.movies, "id");
    },
    filteredMovies() {
      if (!this.filter) {
        return this.uniqueMovies.sort((a, b) => (a.id > b.id ? 1 : -1));
      }

      if (this.filter == "alpha") {
        return this.uniqueMovies.sort((a, b) =>
          a.sortable_name > b.sortable_name ? 1 : -1
        );
      }

      if (this.filter == "chrono") {
        return this.uniqueMovies
          .map((m) => {
            let [year, month, day] = m.release_date.split("-");
            return { ...m, sortable_date: `${month}${day}${year}` };
          })
          .sort((a, b) => (a.release_date > b.release_date ? 1 : -1));
      }
    },
    filteredAllMovies() {
      if (!this.filter) {
        return this.allMovies;
      }

      if (this.filter == "alpha") {
        return this.allMovies.sort((a, b) => (a.title > b.title ? 1 : -1));
      }

      if (this.filter == "chrono") {
        return this.allMovies.sort((a, b) =>
          a.release_date > b.release_date ? 1 : -1
        );
      }
    },
  },

  methods: {
    close() {
      this.setCreditMovieDialog(false).then(() => {
        this.tabs = "catalog";
        this.allMovies = [];
        this.wish_listed = false;
      });
    },
    async setMovie(movie) {
      try {
        await this.setActiveMovie(movie);
        this.setCreditMovieDialog(false);
      } catch (error) {
        console.log(error);
      }
    },
    async getAllMovies() {
      const results = await this.searchMoviesForPerson({
        id: this.activeCredit.movie_db_id,
      });

      this.allMovies = results
        .map((r) => {
          let url = "https://image.tmdb.org/t/p/w154";
          let owned = this.mdbids.map((mid) => parseInt(mid)).includes(r.id);

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
    },
    setSearchedMovie(poster) {
      this.setActiveSearchMovie(poster);
      this.setActiveSearchMovieDialog(true);
    },
  },

  setup() {
    const store = useMovieStore();
    const authStore = useAuthStore();
    const { creditMovieDialog, activeCredit, activeSearchedMovie, mdbids } =
      storeToRefs(store);
    const { user } = storeToRefs(authStore);
    const {
      setActiveCredit,
      setCreditMovieDialog,
      setActiveMovie,
      searchMoviesForPerson,
      setActiveSearchMovie,
      setActiveSearchMovieDialog,
    } = store;
    const { xs, smAndDown } = useDisplay();

    return {
      creditMovieDialog,
      setActiveCredit,
      setCreditMovieDialog,
      activeCredit,
      setActiveMovie,
      xs,
      smAndDown,
      searchMoviesForPerson,
      setActiveSearchMovie,
      setActiveSearchMovieDialog,
      activeSearchedMovie,
      user,
      mdbids,
    };
  },
};
</script>