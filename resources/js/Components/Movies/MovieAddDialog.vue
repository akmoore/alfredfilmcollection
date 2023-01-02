<template>
  <v-dialog
    v-model="activeSearchedMovieDialog"
    persistent
    width="800"
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
  >
    <v-card>
      <v-overlay v-model="loading" class="d-flex justify-center align-center">
        <v-progress-circular
          color="white"
          indeterminate
          :width="7"
          :size="128"
        ></v-progress-circular>
      </v-overlay>
      <v-toolbar
        v-if="xs"
        flat
        density="compact"
        style="position: fixed; width: 100%; z-index: 10"
      >
        <v-toolbar-title>Add Movie</v-toolbar-title>
        <v-toolbar-items>
          <!-- <v-btn variant="text" color="success" @click="save" class="">
            Save
          </v-btn> -->
          <v-btn variant="plain" @click="close" icon="mdi-close"> </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text :style="xs ? 'margin-top: 2em;' : ''">
        <v-container v-if="activeSearchedMovie && boutiques">
          <v-row>
            <v-col cols="12" md="4">
              <v-carousel
                v-if="
                  activeSearchedMovieDataFull.images &&
                  activeSearchedMovieDataFull.images.posters.length
                "
                hide-delimiters
                show-arrows="hover"
                height="336"
                progress="primary"
                @update:modelValue="updatePoster($event)"
              >
                <v-carousel-item
                  v-for="(poster, index) in activeSearchedMovieDataFull.images
                    .posters"
                  :key="index"
                  :src="`https://image.tmdb.org/t/p/w300${poster.file_path}`"
                ></v-carousel-item>
              </v-carousel>
              <v-img v-else :src="activeSearchedMovie.poster_path"></v-img>
            </v-col>
            <v-col cols="12" md="8">
              <!-- PUBLICLY VISIBLE -->
              <v-row>
                <v-col>
                  <v-checkbox
                    v-model="form.publicly_visible"
                    label="Publicly Visible"
                    hide-details
                  ></v-checkbox>
                </v-col>
              </v-row>

              <!-- NAME -->
              <v-row>
                <v-col>
                  <v-text-field
                    label="Name"
                    v-model="form.name"
                    clearable
                    :hide-details="!errors.name"
                    :error-messages="errors.name"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- IMAGE -->
              <v-row>
                <v-col>
                  <v-text-field
                    label="Image"
                    v-model="form.image"
                    clearable
                    :hide-details="!errors.image"
                    :error-messages="errors.image"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- YEAR -->
              <v-row>
                <v-col>
                  <v-text-field
                    label="Year"
                    type="date"
                    v-model="form.year"
                    clearable
                    :hide-details="!errors.year"
                    :error-messages="errors.year"
                  ></v-text-field>
                </v-col>
              </v-row>

              <v-row>
                <!-- QUALITY -->
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.quality"
                    label="Quality"
                    item-title="display"
                    item-value="value"
                    :items="[
                      { display: '4K', value: 2160 },
                      { display: 'HD 1080p', value: 1080 },
                      { display: 'HD 720p', value: 720 },
                      { display: 'HD 540p', value: 540 },
                      { display: 'SD', value: 480 },
                    ]"
                    clearable
                    :hide-details="!errors.quality"
                    :error-messages="errors.quality"
                  ></v-select>
                </v-col>

                <!-- MEDIUM -->
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.medium"
                    label="Medium"
                    item-title="display"
                    item-value="value"
                    :items="[
                      { display: 'Disc', value: 'disc' },
                      { display: 'Digital', value: 'digital' },
                    ]"
                    clearable
                    :hide-details="!errors.medium"
                    :error-messages="errors.medium"
                  ></v-select>
                </v-col>
              </v-row>

              <!-- BOUTIQUES -->
              <v-row>
                <v-col>
                  <v-select
                    v-model="form.boutique"
                    label="Boutique Distributor"
                    item-title="display"
                    item-value="id"
                    :items="boutiques"
                    clearable
                    :hide-details="!errors.boutique"
                    :error-messages="errors.boutique"
                    @update:modelValue="limitCollection"
                  >
                    <template v-slot:append>
                      <v-icon
                        icon="mdi:mdi-alpha-b-circle"
                        @click="addBoutique"
                      ></v-icon>
                    </template>
                  </v-select>
                </v-col>
              </v-row>

              <!-- COLLECTIONS -->
              <v-row>
                <v-col>
                  <v-autocomplete
                    v-model="form.collection"
                    label="Collection"
                    :items="collectionStore.filteredCollections"
                    item-title="name"
                    item-value="id"
                    clearable
                    hide-details
                    return-object
                    @update:modelValue="collectionChanged($event)"
                  >
                    <template v-slot:append>
                      <v-icon
                        icon="mdi:mdi-alpha-c-circle"
                        @click="setCollectionDialog(true)"
                      ></v-icon>
                    </template>
                  </v-autocomplete>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-btn
          v-if="!xs"
          variant="plain"
          color="error"
          @click="close"
          class="ml-4 mb-2"
        >
          Cancel
        </v-btn>
        <v-spacer v-if="!xs"></v-spacer>
        <v-btn
          variant="plain"
          :block="xs"
          color="purple-accent-1"
          @click="save"
          class="mr-4 mb-2"
        >
          Submit
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <BoutiqueStoreDialog @boutique="assignBoutique" />
  <CollectionStoreDialog
    :boutique="this.form.boutique"
    @close="setCollection"
  />
</template>

<script>
import intus from "intus";
import { isRequired, isMin, isNumeric, isIn } from "intus/rules";
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import { useCollectionStore } from "@/Store/CollectionStore.js";
import { useDisplay } from "vuetify";
import BoutiqueStoreDialog from "@/Components/Boutiques/BoutiqueStoreDialog.vue";
import CollectionStoreDialog from "@/Components/Collection/CollectionStoreDialog.vue";
export default {
  name: "MovieAddDialog",
  components: {
    BoutiqueStoreDialog,
    CollectionStoreDialog,
  },

  props: {
    wishListed: Boolean,
  },

  created() {
    this.setInitialMovieData();
    this.getBoutiques();
  },

  data() {
    return {
      dialog: false,
      loading: false,
      form: {
        name: "",
        image: "",
        year: "",
        quality: "",
        medium: "",
        boutique: null,
        collection: null,
        manual: false,
        publicly_visible: true,
        wish_listed: this.wishListed,
      },
      errors: {},
    };
  },

  methods: {
    async save() {
      try {
        this.loading = true;
        this.errors = {};

        const validate = intus.validate(
          {
            ...this.form,
            quality: this.form.quality.toString(),
            // boutique: this.form.boutique.toString(),
          },
          {
            name: [isRequired(), isMin(2)],
            image: [isRequired()],
            year: [isRequired()],
            quality: [isRequired(), isIn("2160", "1080", "720", "540", "480")],
            medium: [isRequired(), isIn("digital", "disc")],
            boutique: [isNumeric()],
          }
        );

        if (!validate.passes()) {
          this.errors = validate.errors();
          return;
        }

        let collection = this.form.collection ? this.form.collection.id : null;

        await this.storeMovie({
          movie_data: this.activeSearchedMovie,
          form: { ...this.form, collection },
          movie_full_data: this.activeSearchedMovieDataFull,
        });

        this.close();
      } catch (error) {
        return error;
      }
    },
    close() {
      this.loading = false;
      this.setActiveSearchMovieDialog(false);
      setTimeout(() => {
        this.setActiveSearchMovie(null);
      }, 1000);
    },
    setInitialMovieData() {
      if (this.activeSearchedMovie) {
        let { title, release_date, poster_id } = this.activeSearchedMovie;
        this.form = {
          ...this.form,
          name: title,
          year: release_date,
          image: poster_id,
        };
      }
    },
    addBoutique() {
      this.setBoutiqueDialog(true);
    },
    async assignBoutique(boutique) {
      try {
        let result = await boutique;
        this.form.boutique = result.id;
      } catch (error) {
        console.log(error);
      }
    },
    updatePoster(number) {
      let poster = this.activeSearchedMovieDataFull.images.posters[number];
      this.form.image = `${poster.file_path}`;
    },
    setCollection(collection) {
      this.form.collection = collection.id;
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
  },

  setup() {
    const { mobile, xs } = useDisplay();
    const store = useMovieStore();
    const {
      activeSearchedMovie,
      activeSearchedMovieDialog,
      activeSearchedMovieDataFull,
    } = storeToRefs(store);
    const { setActiveSearchMovieDialog, setActiveSearchMovie, storeMovie } =
      store;

    const boutiqueStore = useBoutiqueStore();
    const { boutiques } = storeToRefs(boutiqueStore);
    const { setBoutiqueDialog, getBoutiques } = boutiqueStore;

    const collectionStore = useCollectionStore();
    const { collections } = storeToRefs(collectionStore);
    const { setCollectionDialog, setFilteredCollections } = collectionStore;

    return {
      store,
      activeSearchedMovie,
      activeSearchedMovieDialog,
      setActiveSearchMovieDialog,
      setActiveSearchMovie,
      mobile,
      xs,
      storeMovie,
      setBoutiqueDialog,
      boutiques,
      getBoutiques,
      activeSearchedMovieDataFull,
      collections,
      setCollectionDialog,
      collectionStore,
      setFilteredCollections,
    };
  },
};
</script>