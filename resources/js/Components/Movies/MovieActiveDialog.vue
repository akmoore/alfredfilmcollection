<template>
  <v-dialog
    v-model="activeMovieDialog"
    fullscreen
    :scrim="false"
    transition="dialog-bottom-transition"
  >
    <v-card v-if="!activeMovie">
      <v-card-text>
        <v-progress-circular
          color="primary"
          indeterminate
          :size="77"
        ></v-progress-circular>
      </v-card-text>
    </v-card>
    <v-card v-else>
      <v-toolbar
        color="grey-darken-3"
        density="compact"
        tile
        flat
        style="position: fixed; width: 100%; z-index: 10"
      >
        <v-toolbar-title>
          <v-icon
            size="small"
            color="grey-darken-2"
            icon="mdi:mdi-filmstrip-box"
            class="mb-1"
          ></v-icon>
          {{ activeMovie.name }}
          <v-icon
            size="x-small"
            class="mb-1"
            color="purple-lighten-3"
            icon="mdi:mdi-alpha-m-box"
            v-if="activeMovie.manual"
          ></v-icon>
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <MovieNoteDialog
            :movie="activeMovie"
            v-if="user || activeMovie.notes.length"
          />
          <v-menu v-if="user">
            <template v-slot:activator="{ props }">
              <v-btn icon="mdi-dots-vertical" v-bind="props"></v-btn>
            </template>

            <v-list>
              <v-list-item @click="editMovie" v-if="!activeMovie.manual">
                <template v-slot:prepend>
                  <v-icon
                    size="x-small"
                    icon="mdi:mdi-pencil"
                    class="mr-2 mb-1"
                  ></v-icon>
                </template>
                <v-list-item-title> Edit </v-list-item-title>
              </v-list-item>
              <v-list-item @click="makeEditable" v-else>
                <template v-slot:prepend>
                  <v-icon
                    size="x-small"
                    icon="mdi:mdi-pencil"
                    class="mr-2 mb-1"
                  ></v-icon>
                </template>
                <v-list-item-title>
                  {{ !editable ? "Make Editable" : "Unmake Editable" }}
                </v-list-item-title>
              </v-list-item>
              <v-divider></v-divider>
              <v-list-item @click="deleteMovie(false)">
                <template v-slot:prepend>
                  <v-icon
                    size="x-small"
                    icon="mdi:mdi-delete"
                    class="mr-2"
                  ></v-icon>
                </template>
                <v-list-item-title> Delete </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
          <v-btn
            icon="mdi:mdi-close"
            variant="text"
            @click="unsetActiveMovie"
          >
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-container :fluid="!activeMovie.manual" style="margin-top: 50px">
        <v-row>
          <!-- ------------ -->
          <!-- MOVIE POSTER -->
          <!-- ------------ -->
          <v-col cols="12" :md="!activeMovie.manual ? 4 : 6">
            <v-card
              elevation="10"
              :style="
                sm
                  ? 'width:29%; margin-bottom: -50em; margin-top: 2.3em;margin-left: 1em;z-index:1;'
                  : ''
              "
            >
              <div
                v-if="activeMovie.quality == 2160"
                style="
                  position: absolute;
                  top: 10px;
                  right: 10px;
                  z-index: 10;
                  mix-blend-mode: difference;
                "
              >
                <v-icon size="x-large" icon="mdi:mdi-video-4k-box"></v-icon>
              </div>
              <v-img
                :src="`storage/posters/${this.activeMovie['paths']['poster_large']}`"
                :lazy-src="`/posters/${this.activeMovie['paths']['poster_large']}`"
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
            </v-card>
          </v-col>

          <!-- ---------------------- -->
          <!-- MIDDLE SECTION/DETAILS -->
          <!-- ---------------------- -->
          <v-col cols="12" :md="!activeMovie.manual ? 5 : 6">
            <!-- -------- -->
            <!-- BACKDROP -->
            <!-- -------- -->
            <v-row v-if="!activeMovie.manual">
              <v-col>
                <v-img
                  :src="`storage/backdrops/${activeMovie['paths']['backdrop']}`"
                  :lazy-src="`/backdrops/${activeMovie['paths']['backdrop']}`"
                >
                  <v-toolbar density="compact" color="rgba(0, 0, 0, 0)">
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                      <MovieYouTubeLinksDialog
                        :videos="activeMovie.videos"
                        v-if="activeMovie.videos"
                      />
                    </v-toolbar-items>
                  </v-toolbar>

                  <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                      <v-progress-circular
                        indeterminate
                        color="grey-lighten-4"
                      ></v-progress-circular>
                    </div>
                  </template>
                </v-img>
              </v-col>
            </v-row>

            <!-- ------- -->
            <!-- DETAILS -->
            <!-- ------- -->
            <v-row>
              <v-col>
                <v-card
                  flat
                  hover
                  rounded="0"
                  variant="tonal"
                  :style="
                    activeMovie.manual
                      ? 'margin-top: 0px;'
                      : 'margin-top: -70px;'
                  "
                  color="black"
                >
                  <v-list>
                    <!-- ------- -->
                    <!-- WATCHED -->
                    <!-- ------- -->
                    <v-list-item>
                      <v-switch
                        v-if="user"
                        inset
                        color="purple-lighten-3"
                        hide-details
                        v-model="activeMovie.watched"
                        @change="setWatched"
                      ></v-switch>
                      <div v-else>
                        <v-icon
                          v-if="activeMovie.watched"
                          icon="mdi:mdi-eye-outline"
                          size="small"
                        ></v-icon>
                        <v-icon
                          size="small"
                          v-else
                          icon="mdi:mdi-eye-off-outline"
                        ></v-icon>
                      </div>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          watch
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------ -->
                    <!-- RATING -->
                    <!-- ------ -->
                    <v-list-item>
                      <v-rating
                        v-if="user"
                        v-model="activeMovie.rating"
                        bg-color="orange-lighten-1"
                        color="purple-lighten-4"
                        @change="setRating"
                      ></v-rating>
                      <div v-else>
                        <span v-if="activeMovie.rating">
                          <v-icon
                            v-for="i in activeMovie.rating"
                            :key="i"
                            icon="mdi:mdi-star"
                            size="small"
                            color="purple-accent-1"
                          ></v-icon>
                        </span>
                        <span v-else>
                          <v-icon
                            color="grey-darken-1"
                            icon="mdi:mdi-star-off"
                            size="small"
                          ></v-icon>
                        </span>
                      </div>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          Rating
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- --------- -->
                    <!-- IMDB LINK -->
                    <!-- --------- -->
                    <v-list-item v-if="activeMovie.imdb_id">
                      <v-list-item-title>
                        <a
                          target="_blank"
                          :href="`https://www.imdb.com/title/${activeMovie.imdb_id}/`"
                          style="text-decoration: none; color: #b597d1"
                        >
                          <span v-if="!xs"
                            >www.imdb.com/title/{{ activeMovie.imdb_id }}</span
                          >
                          <span v-else>{{ activeMovie.imdb_id }}</span>
                        </a>
                      </v-list-item-title>
                      <template v-slot:prepend>
                        <v-icon icon="mdi:mdi-link-variant"></v-icon>
                      </template>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          <span v-if="!xs">IMDB LINK</span>
                          <span v-else>IMDB ID</span>
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------------- -->
                    <!-- CERTIFICATION -->
                    <!-- ------------- -->
                    <v-list-item>
                      <v-list-item-title>
                        <span v-if="!editable">{{
                          activeMovie.certification
                        }}</span>
                        <v-select
                          v-else
                          :items="certsArray"
                          v-model="activeMovie.certification"
                          variant="underlined"
                          style="width: 90%"
                          @update:modelValue="
                            updateValue(
                              activeMovie.certification,
                              'certification'
                            )
                          "
                        ></v-select>
                      </v-list-item-title>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          certification
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------- -->
                    <!-- RUNTIME -->
                    <!-- ------- -->
                    <v-list-item>
                      <v-list-item-title>
                        <span v-if="!editable">{{
                          `${activeMovie.runtime} mins`
                        }}</span>
                        <v-text-field
                          v-else
                          v-model="activeMovie.runtime"
                          variant="underlined"
                          density="compact"
                          type="number"
                          style="width: 90%"
                          @update:modelValue="
                            updateValue(activeMovie.runtime, 'runtime')
                          "
                        ></v-text-field>
                      </v-list-item-title>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          Runtime
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------------ -->
                    <!-- RELEASE DATE -->
                    <!-- ------------ -->
                    <v-list-item>
                      <v-list-item-title v-if="!editable">
                        {{ releaseDateFormat }}
                      </v-list-item-title>
                      <v-text-field
                        v-else
                        v-model="activeMovie.release_date"
                        variant="underlined"
                        type="date"
                        style="width: 90%"
                        @update:modelValue="
                          updateValue(activeMovie.release_date, 'release_date')
                        "
                      ></v-text-field>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          Release Date
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------------------------ -->
                    <!-- FILTER ON QUALITY/FORMAT -->
                    <!-- ------------------------ -->
                    <v-list-item>
                      <v-list-item-title>
                        <span v-if="!editable">{{
                          activeMovie.filter_on_quality
                            .split("_")
                            .join(" ")
                            .toUpperCase()
                        }}</span>
                        <v-select
                          v-else
                          :items="formatsArray"
                          item-title="display"
                          item-value="value"
                          v-model="activeMovie.filter_on_quality"
                          variant="underlined"
                          style="width: 90%"
                          @update:modelValue="
                            updateValue(
                              activeMovie.filter_on_quality,
                              'filter_on_quality'
                            )
                          "
                        ></v-select>
                      </v-list-item-title>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          Format
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------ -->
                    <!-- MANUAL -->
                    <!-- ------ -->
                    <div v-if="!activeMovie.manual">
                      <v-list-item :title="formatMoney()">
                        <template v-slot:append>
                          <span class="text-purple-lighten-3 text-overline">
                            Revenue
                          </span>
                        </template>
                      </v-list-item>
                      <v-divider></v-divider>
                    </div>

                    <!-- ------ -->
                    <!-- GENRES -->
                    <!-- ------ -->
                    <v-list-item>
                      <v-list-item-title>
                        <span v-if="!editable">{{ genres().names }}</span>
                        <v-autocomplete
                          v-else
                          density="compact"
                          variant="underlined"
                          :items="genresArray"
                          item-title="name"
                          item-value="id"
                          multiple
                          v-model="model.genres"
                          style="width: 90%"
                          @update:modelValue="
                            updateValue(model.genres, 'movie_genres')
                          "
                        ></v-autocomplete>
                      </v-list-item-title>
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          {{ genres().count > 0 ? "genres" : "genre" }}
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- -------- -->
                    <!-- BOUTIQUE -->
                    <!-- -------- -->
                    <v-list-item
                      :title="
                        activeMovie.boutique
                          ? activeMovie.boutique.display
                          : 'N/A'
                      "
                    >
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          Boutique
                        </span>
                      </template>

                      <template v-if="activeMovie.boutique" v-slot:prepend>
                        <v-avatar size="28" rounded="0">
                          <v-img
                            v-if="activeMovie.boutique.image"
                            :src="`storage/boutiques/${activeMovie.boutique.image}`"
                          ></v-img>
                          <v-icon v-else color="white">mdi-folder</v-icon>
                        </v-avatar>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ---------- -->
                    <!-- COLLECTION -->
                    <!-- ---------- -->
                    <v-list-item>
                      <CollectionShowDialog
                        :collection="activeMovie.collection"
                      />
                      <template v-slot:append>
                        <span class="text-purple-lighten-3 text-overline">
                          <!-- <v-icon icon="mdi:mdi-filmstrip-box-multiple" class="mr-1"></v-icon> -->
                          Collection
                        </span>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ------- -->
                    <!-- TAGLINE -->
                    <!-- ------- -->
                    <v-list-item>
                      {{ activeMovie.tagline }}
                      <!-- <v-list-item-subtitle
                        class="text-purple-lighten-3 text-overline"
                      >
                        Tagline
                      </v-list-item-subtitle> -->
                      <template v-slot:append>
                        <v-tooltip text="Tagline" location="start">
                          <template v-slot:activator="{ props }">
                            <v-icon
                              v-bind="props"
                              color="purple-lighten-3"
                              icon="mdi:mdi-alpha-t-circle"
                            ></v-icon>
                          </template>
                        </v-tooltip>
                      </template>
                    </v-list-item>
                    <v-divider></v-divider>

                    <!-- ----------- -->
                    <!-- DESCRIPTION -->
                    <!-- ----------- -->
                    <v-list-item>
                      <span class="text-grey-lighten-1">
                        {{ activeMovie.description }}
                      </span>
                      <template v-slot:append>
                        <v-tooltip text="Description" location="start">
                          <template v-slot:activator="{ props }">
                            <v-icon
                              v-bind="props"
                              color="purple-lighten-3"
                              icon="mdi:mdi-alpha-d-circle"
                            ></v-icon>
                          </template>
                        </v-tooltip>
                      </template>
                    </v-list-item>
                  </v-list>
                </v-card>
              </v-col>
            </v-row>
          </v-col>

          <!-- ----------- -->
          <!-- CAST & CREW -->
          <!-- ----------- -->
          <v-col cols="12" md="3" v-if="!activeMovie.manual">
            <v-card
              flat
              hover
              rounded="0"
              variant="tonal"
              color="black"
              style="height: 85vh"
            >
              <v-toolbar flat density="compact">
                <v-toolbar-title class="text-overline">
                  <v-icon
                    size="large"
                    icon="mdi:mdi-account-group"
                    class="mb-2"
                    color="grey-darken-1"
                  ></v-icon>
                  CREDITS
                </v-toolbar-title>
              </v-toolbar>
              <v-list
                lines="two"
                style="height: calc(100% - 48px); overflow-y: auto"
              >
                <v-list-subheader>CREW</v-list-subheader>
                <template v-for="(member, index) in crew" :key="member.id">
                  <v-list-item
                    :title="member.name"
                    :subtitle="member.pivot.role"
                    @click="setActiveCredit(member)"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="grey">
                        <v-icon
                          v-if="!member.profile_pic"
                          icon="mdi:mdi-account"
                        ></v-icon>
                        <v-img
                          v-else
                          cover
                          :src="`storage/credits/${member.profile_pic}`"
                        ></v-img>
                      </v-avatar>
                    </template>
                  </v-list-item>
                  <v-divider inset v-if="index < crew.length - 1"></v-divider>
                </template>

                <v-list-subheader>CAST</v-list-subheader>
                <template v-for="(actor, index) in cast" :key="actor.id">
                  <v-list-item
                    :title="actor.name"
                    :subtitle="actor.pivot.character"
                    @click="setActiveCredit(actor)"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="grey">
                        <v-icon
                          v-if="!actor.profile_pic"
                          icon="mdi:mdi-account"
                        ></v-icon>
                        <v-img
                          v-else
                          cover
                          :src="`storage/credits/${actor.profile_pic}`"
                        ></v-img>
                      </v-avatar>
                    </template>
                  </v-list-item>
                  <v-divider inset v-if="index < cast.length - 1"></v-divider>
                </template>
              </v-list>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <MovieCreditsMoviesDialog />
    <MovieEditDialog v-if="editableMovie" />
    <MovieDeleteConfirmationDialog @movieDeleted="deleteMovie(true)" />
  </v-dialog>
</template>

<script>
import genres from "./Support/genres.js";
import currency from "currency.js";
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/Store/AuthStore.js";
import { useMovieStore } from "@/Store/MovieStore.js";
import MovieEditDialog from "@/Components/Movies/MovieEditDialog.vue";
import MovieYouTubeLinksDialog from "@/Components/Movies/MovieYouTubeLinksDialog.vue";
import MovieCreditsMoviesDialog from "@/Components/Movies/MovieCreditsMoviesDialog.vue";
import MovieDeleteConfirmationDialog from "@/Components/Movies/MovieDeleteConfirmationDialog.vue";
import CollectionShowDialog from "@/Components/Collection/CollectionShowDialog.vue";
import { useDisplay } from "vuetify";
import MovieNoteDialog from "@/Components/Movies/MovieNoteDialog.vue";

export default {
  components: {
    MovieYouTubeLinksDialog,
    MovieCreditsMoviesDialog,
    MovieEditDialog,
    MovieDeleteConfirmationDialog,
    CollectionShowDialog,
    MovieNoteDialog,
  },

  created() {},

  data() {
    return {
      drawer: false,
      poster: null,
      editable: false,
      genresArray: genres,
      model: {
        genres: [],
      },
      formatsArray: [
        { display: "4K DISC", value: "4k_disc" },
        { display: "4K DIGITAL", value: "4k_digital" },
        { display: "HD DISC", value: "hd_disc" },
        { display: "HD DIGITAL", value: "hd_digital" },
        { display: "SD DISC", value: "sd_disc" },
        { display: "SD DIGITAL", value: "sd_digital" },
      ],
      certsArray: ["G", "PG", "PG-13", "R", "NC-17", "NR"],
    };
  },

  watch: {
    activeMovie(newVal, oldVal) {
      //   console.log({ newVal, oldVal });
      if (newVal) {
        this.model.genres = newVal.movie_genres.map((mg) => parseInt(mg.id));
      }
    },
  },

  computed: {
    cast() {
      return this.activeMovie.people
        .filter((p) => p.pivot.role == "Acting")
        .sort((a, b) => (a.pivot.order > b.pivot.order ? 1 : -1));
    },
    crew() {
      return this.activeMovie.people.filter((p) => !p.pivot.cast);
    },
    remappedGenres() {
      return this.activeMovie.movie_genres.map((mg) => {
        return { id: mg.movie_db_id, name: mg.name };
      });
    },
    releaseDateFormat() {
      if (this.activeMovie.release_date) {
        let [year, month, day] = this.activeMovie.release_date.split("-");
        let outputMonth = {
          "01": "January",
          "02": "February",
          "03": "March",
          "04": "April",
          "05": "May",
          "06": "June",
          "07": "July",
          "08": "August",
          "09": "September",
          10: "October",
          11: "November",
          12: "December",
        }[month];

        return `${outputMonth} ${day}, ${year}`;
      }

      return "";
    },
  },

  methods: {
    genres() {
      let genres = this.activeMovie.movie_genres
        ? this.activeMovie.movie_genres.map((g) => g.name)
        : null;
      let names = genres ? genres.join(", ") : null;
      let count = genres ? genres.length : null;

      return { names, count };
    },
    formatMoney() {
      return this.activeMovie.revenue
        ? currency(this.activeMovie.revenue).format()
        : currency(0).format();
    },
    setWatched() {
      this.setActiveWatched(this.activeMovie.watched);
    },
    setRating() {
      this.setActiveRating(this.activeMovie.rating);
    },
    updateValue(value, type) {
      if (type == "movie_genres") {
        let values = this.genresArray.filter((g) => value.includes(g.id));
        this.setSingleUpdate(values, type);
        return;
      }

      this.setSingleUpdate(value, type);
    },
    openCreditsRelationsDrawer() {
      this.drawer = true;
    },
    deleteMovie(isDeleted = false) {
      if (!isDeleted) {
        this.setDeleteConfirmationDialog(true);
      }

      if (isDeleted) {
        this.getMovies();
        setTimeout(() => {
          this.unsetActiveMovie();
        }, 500);
      }
    },
    async editMovie() {
      try {
        await this.setEditableMovie();
        this.setEditMovieDialog(true);
      } catch (error) {}
    },
    makeEditable() {
      this.editable = !this.editable;
    },
  },

  setup() {
    let store = useMovieStore();
    let authStore = useAuthStore();
    let { activeMovieDialog, activeMovie, editableMovie } = storeToRefs(store);
    let { user } = storeToRefs(authStore);
    let {
      unsetActiveMovie,
      setActiveRating,
      setActiveWatched,
      setActiveCredit,
      setEditMovieDialog,
      setEditableMovie,
      setDeleteConfirmationDialog,
      getMovies,
      setSingleUpdate,
    } = store;
    const { xs, sm } = useDisplay();

    return {
      activeMovieDialog,
      unsetActiveMovie,
      activeMovie,
      setActiveRating,
      setActiveWatched,
      setActiveCredit,
      setEditMovieDialog,
      setEditableMovie,
      editableMovie,
      setDeleteConfirmationDialog,
      getMovies,
      user,
      setSingleUpdate,
      xs,
      sm,
    };
  },
};
</script>