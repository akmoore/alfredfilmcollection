<template>
  <v-dialog
    v-model="editMovieDialog"
    width="1455"
    persistent
    :scrim="!xs"
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
  >
    <v-card v-if="editableMovie" min-height="650">
      <v-toolbar
        density="compact"
        style="position: fixed; width: 100%; z-index: 10"
      >
        <v-toolbar-title>{{ form.name }}</v-toolbar-title>
        <v-toolbar-items>
          <v-btn variant="text" color="purple-accent-1" @click="editMovie">
            save
          </v-btn>
          <v-btn variant="text" @click="close" icon="mdi:mdi-close"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text style="margin-top: 50px">
        <v-overlay v-model="loading" class="d-flex justify-center align-center">
          <v-progress-circular
            color="white"
            indeterminate
            :size="128"
          ></v-progress-circular>
        </v-overlay>
        <v-row>
          <v-col cols="12" md="3">
            <v-card tile flat theme="light">
              <v-toolbar theme="light" density="compact" flat>
                <v-toolbar-title>Poster</v-toolbar-title>
              </v-toolbar>

              <v-carousel
                hide-delimiters
                show-arrows="hover"
                progress="primary"
                @update:modelValue="updatePoster($event)"
                :model-value="form.poster_index"
              >
                <v-carousel-item
                  v-for="(poster, index) in editableMovie.images.posters"
                  :key="index"
                  :src="`https://image.tmdb.org/t/p/w1280${poster.file_path}`"
                ></v-carousel-item>
              </v-carousel>
            </v-card>
          </v-col>
          <v-col cols="12" md="6">
            <v-card theme="light">
              <v-toolbar density="compact" flat>
                <v-toolbar-title>Backdrop</v-toolbar-title>
              </v-toolbar>
              <v-carousel
                hide-delimiters
                show-arrows="hover"
                progress="primary"
                @update:modelValue="updateBackdrop($event)"
                :model-value="form.backdrop_index"
              >
                <v-carousel-item
                  v-for="(backdrop, index) in editableMovie.images.backdrops"
                  :key="index"
                  :src="`https://image.tmdb.org/t/p/w780${backdrop.file_path}`"
                ></v-carousel-item>
              </v-carousel>
            </v-card>
          </v-col>
          <v-col cols="12" md="3">
            <v-row>
              <v-col>
                <v-checkbox
                  v-model="form.publicly_visible"
                  label="Publicly Visible"
                  hide-details
                ></v-checkbox>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  clearable
                  label="Name"
                  v-model="form.name"
                  :hide-details="!errors.name"
                  :error-messages="errors.name"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-select
                  clearable
                  label="Certification"
                  :items="certifications"
                  v-model="form.certification"
                  :hide-details="!errors.certification"
                  :error-messages="errors.certification"
                ></v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  clearable
                  label="Runtime"
                  v-model="form.runtime"
                  hint="Runtime In Minutes"
                  :hide-details="!errors.runtime"
                  :error-messages="errors.runtime"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  clearable
                  type="date"
                  label="Release Date"
                  v-model="form.release_date"
                  :hide-details="!errors.release_date"
                  :error-messages="errors.release_date"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-select
                  clearable
                  label="Format"
                  :items="formats"
                  item-title="display"
                  item-value="value"
                  v-model="form.filter_on_quality"
                  :hide-details="!errors.filter_on_quality"
                  :error-messages="errors.filter_on_quality"
                ></v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-autocomplete
                  clearable
                  label="Boutique"
                  :items="boutiques"
                  item-title="display"
                  item-value="id"
                  v-model="form.boutique"
                  :hide-details="!errors.boutique"
                  :error-messages="errors.boutique"
                  @update:modelValue="limitCollection"
                ></v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-autocomplete
                  clearable
                  label="Collection"
                  :items="collectionStore.filteredCollections"
                  item-title="name"
                  item-value="id"
                  v-model="form.collection"
                  :hide-details="!errors.collection"
                  :error-messages="errors.collection"
                  return-object
                  @update:modelValue="collectionChanged($event)"
                ></v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" class="d-flex justify-space-between">
                <!-- <v-btn variant="outlined" @click="close">cancel</v-btn>
                <v-btn
                  variant="outlined"
                  color="purple-accent-1"
                  @click="editMovie"
                  >save</v-btn
                > -->
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import intus from "intus";
import { storeToRefs } from "pinia";
import { useDisplay } from "vuetify";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import { useCollectionStore } from "@/Store/CollectionStore.js";
import { isRequired, isMin, isNumeric, isIn } from "intus/rules";
export default {
  name: "MovieEditDialog",

  created() {
    if (this.editableMovie) {
      this.form.poster_index = this.editableMovie.poster_index;
      this.form.active_poster =
        this.editableMovie.images.posters[this.editableMovie.poster_index];
      this.form.backdrop_index = this.editableMovie.backdrop_index;
      this.form.active_backdrop =
        this.editableMovie.images.backdrops[this.editableMovie.backdrop_index];
      this.form.certification = this.editableMovie.certification;
      this.form.runtime = this.editableMovie.runtime;
      this.form.release_date = this.editableMovie.release_date;
      this.form.filter_on_quality = this.editableMovie.filter_on_quality;
      this.form.name = this.editableMovie.name;
      this.form.boutique = this.editableMovie.boutique_id;
      this.form.collection = this.findCollection(
        this.editableMovie.collection_id
      );
      this.form.publicly_visible = this.editableMovie.publicly_visible;
    }
  },

  data() {
    return {
      form: {
        name: null,
        poster_index: null,
        active_poster: null,
        backdrop_index: null,
        active_backdrop: null,
        certification: null,
        runtime: null,
        release_date: null,
        filter_on_quality: null,
        boutique: null,
        collection: null,
        publicly_visible: null,
      },
      certifications: ["G", "PG", "PG-13", "R", "NC-17", "NR"],
      formats: [
        { display: "4K DISC", value: "4k_disc" },
        { display: "4K DIGITAL", value: "4k_digital" },
        { display: "HD DISC", value: "hd_disc" },
        { display: "HD DIGITAL", value: "hd_digital" },
        { display: "SD DISC", value: "sd_disc" },
        { display: "SD DIGITAL", value: "sd_digital" },
      ],
      loading: false,
      errors: {},
    };
  },

  computed: {
    currentPosterIndex() {
      if (this.editableMovie && this.editableMovie.images.length) {
        return this.editableMovie.images.posters.findIndex((p) => {
          p.file_path == this.editableMovie.poster_id;
        });
      }

      return -1;
    },
  },

  methods: {
    close() {
      this.setEditMovieDialog(false);
      setTimeout(() => this.unsetEditableMovie(), 1000);
    },
    updatePoster(event) {
      this.form.poster_index = event;
      this.form.active_poster = this.editableMovie.images.posters[event];
    },
    updateBackdrop(event) {
      this.form.backdrop_index = event;
      this.form.active_backdrop = this.editableMovie.images.backdrops[event];
    },
    async editMovie() {
      const validate = intus.validate(
        this.form,
        {
          name: [isRequired(), isMin(2)],
          active_poster: [isRequired()],
          active_backdrop: [isRequired()],
          certification: [
            isRequired(),
            isIn("G", "PG", "PG-13", "R", "NC-17", "NR"),
          ],
          runtime: [isRequired(), isNumeric()],
          release_date: [isRequired()],
          filter_on_quality: [
            isRequired(),
            isIn(
              "4k_disc",
              "4k_digital",
              "hd_disc",
              "hd_digital",
              "sd_disc",
              "sd_digital"
            ),
          ],
          boutique: [isNumeric()],
        },
        {
          filter_on_quality: "format",
        }
      );

      if (!validate.passes()) {
        this.errors = validate.errors();
        return;
      }

      this.loading = true;

      try {
        await this.updateMovie({
          form: {
            ...this.form,
            collection: this.form.collection ? this.form.collection.id : null,
          },
          movie: this.editableMovie,
        });

        this.getMovies();
        this.setEditMovieDialog(false);
        setTimeout(() => {
          this.unsetEditableMovie();
        }, 1000);
        this.loading = false;
      } catch (error) {}
    },
    limitCollection() {
      this.setFilteredCollections(this.form.boutique);
      if (
        this.form.collection &&
        !this.collectionStore.filteredCollections
          .map((fc) => fc.id)
          .includes(this.form.collection)
      ) {
        this.form.collection = null;
      }
    },
    setCollection(collection) {
      this.form.collection = collection;
      this.form.boutique = collection.boutique_id;
    },
    collectionChanged(event) {
      if (event && event.boutique_id) {
        this.form.boutique = event.boutique_id;
      }
    },
    findCollection(collectionId) {
      return this.collections.find((c) => c.id == collectionId);
    },
  },

  setup() {
    const store = useMovieStore();
    const boutiqueStore = useBoutiqueStore();
    const collectionStore = useCollectionStore();
    const { editMovieDialog, editableMovie } = storeToRefs(store);
    const { setEditMovieDialog, unsetEditableMovie, updateMovie, getMovies } =
      store;
    const { boutiques } = storeToRefs(boutiqueStore);
    const { collections } = storeToRefs(collectionStore);
    const { setFilteredCollections } = collectionStore;
    const { xs } = useDisplay();

    return {
      editMovieDialog,
      setEditMovieDialog,
      editableMovie,
      unsetEditableMovie,
      updateMovie,
      getMovies,
      boutiques,
      collectionStore,
      setFilteredCollections,
      collections,
      xs,
    };
  },
};
</script>

