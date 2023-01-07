<template>
  <v-dialog
    v-model="dialog"
    persistent
    width="1200"
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
  >
    <template v-slot:activator="{ props: dialogprops }">
      <v-tooltip text="Open Multi-Select Dialog" location="top">
        <template v-slot:activator="{ props: tooltip }">
          <v-btn
            color="purple-accent-1"
            v-bind="{ ...dialogprops, ...tooltip }"
            variant="plain"
            icon="mdi:mdi-open-in-new"
          ></v-btn>
        </template>
      </v-tooltip>
    </template>

    <v-card>
      <v-overlay v-model="loading" class="d-flex justify-center align-center">
        <v-progress-circular
          color="white"
          indeterminate
          :width="15"
          :size="128"
        ></v-progress-circular>
      </v-overlay>
      <v-toolbar
        density="compact"
        :style="xs ? 'position: fixed; width: 100%; z-index: 10;' : ''"
      >
        <v-toolbar-title>
          Multi Select
          <v-chip color="purple-accent-1" label class="ml-3" prepend-icon="mdi-counter">
            {{ movies.length }}
          </v-chip>
        </v-toolbar-title>
        <v-toolbar-items class="d-flex align-center">
          <v-btn color="purple-accent-1" variant="plain" @click="submit">
            Save
          </v-btn>
          <v-btn icon="mdi:mdi-close" @click="close('close')"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text :style="xs ? 'margin-top: 2em;' : ''">
        <v-container fluid>
          <v-row>
            <v-col cols="12" sm="6" md="4" :order="!xs ? 1 : 2">
              <v-card flat tile>
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
                <v-row>
                  <v-col>
                    <v-select
                      v-model="form.quality"
                      :items="quality"
                      item-title="display"
                      item-value="value"
                      label="Quality"
                      :hide-details="!form.errors.quality"
                      :error-messages="form.errors.quality"
                      clearable
                    ></v-select>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>
                    <v-select
                      v-model="form.medium"
                      :items="medium"
                      item-title="display"
                      item-value="value"
                      label="Medium"
                      clearable
                      :hide-details="!form.errors.medium"
                      :error-messages="form.errors.medium"
                    ></v-select>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>
                    <v-autocomplete
                      v-model="form.boutique"
                      :items="boutiques"
                      item-title="display"
                      item-value="id"
                      label="Boutique"
                      clearable
                      :hide-details="!form.errors.boutique"
                      :error-messages="form.errors.boutique"
                      @update:modelValue="limitCollection"
                    >
                      <template v-slot:append>
                        <v-icon
                          icon="mdi:mdi-alpha-b-circle"
                          @click="setBoutiqueDialog(true)"
                        ></v-icon>
                      </template>
                    </v-autocomplete>
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

                <v-row>
                  <v-col class="d-flex justify-end mt-3">
                    <!-- <v-btn
                      color="purple-accent-1"
                      variant="plain"
                      block
                      @click="submit"
                    >
                      Submit
                    </v-btn> -->
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="8" :order="!xs ? 2 : 1">
              <v-card
                color="grey-lighten-2"
                height="550"
                style="overflow-y: auto"
              >
                <v-card-text>
                  <v-row>
                    <v-col
                      cols="12"
                      sm="6"
                      md="3"
                      v-for="movie in movies"
                      :key="movie.id"
                    >
                      <v-hover v-slot="{ isHovering, props }">
                        <v-card elevation="5" v-bind="props">
                          <v-overlay
                            :model-value="isHovering"
                            contained
                            scrim="#036358"
                            class="d-flex align-center justify-center"
                          >
                            <v-btn
                              variant="plain"
                              size="x-large"
                              @click="removeFromList(movie)"
                            >
                              <v-icon size="large">mdi-close-circle</v-icon>
                            </v-btn>
                          </v-overlay>
                          <v-img :src="movie.poster_path"></v-img>
                        </v-card>
                      </v-hover>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>

    <BoutiqueStoreDialog @boutique="assignBoutique" />
    <CollectionStoreDialog :boutique="form.boutique" @close="setCollection" />
  </v-dialog>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import { useDisplay } from "vuetify";
import { storeToRefs } from "pinia";
import { useForm } from "@inertiajs/inertia-vue3";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useCollectionStore } from "@/Store/CollectionStore.js";
// import BoutiqueStoreDialog from "@/Components/Boutiques/BoutiqueStoreDialog.vue";
// import CollectionStoreDialog from "@/Components/Collection/CollectionStoreDialog.vue";
import axios from "axios";
export default {
  name: "MovieMultiSelectDialog",

  components: {
    BoutiqueStoreDialog: defineAsyncComponent(() =>
      import('@/Components/Boutiques/BoutiqueStoreDialog.vue')
    ),
    CollectionStoreDialog: defineAsyncComponent(() =>
      import('@/Components/Collection/CollectionStoreDialog.vue')
    ),
  },

  props: {
    movies: Array,
    wishListed: Boolean,
  },

  data() {
    return {
      dialog: false,
      quality: [
        { display: "4K", value: 2160 },
        { display: "HD 1080p", value: 1080 },
        { display: "HD 720p", value: 720 },
        { display: "HD 540p", value: 540 },
        { display: "SD", value: 480 },
      ],
      medium: [
        { display: "Disc", value: "disc" },
        { display: "Digital", value: "digital" },
      ],
      moviesFullData: [],
      percentageCount: 0,
      loading: false,
    };
  },

  watch: {
    movies(newVal, oldVal) {
      console.log({ newVal });
    },
  },

  computed: {
    percentage() {
      if (this.movies.length) {
        return Math.round((this.percentageCount / this.movies.length) * 100);
      }

      return 0;
    },
  },

  methods: {
    close(type) {
      this.dialog = !this.dialog;
      this.$emit("close", type);
    },
    async assignBoutique(boutique) {
      try {
        let result = await boutique;
        this.form.boutique = result.id;
      } catch (error) {
        console.log(error);
      }
    },
    removeFromList(movie) {
      this.$emit("remove", movie);
    },
    async getMoviesFullData() {
      try {
        let moviesFullData = [];
        for (const movie of this.movies) {
          let result = await axios.get(
            `${this.baseUrl}/movie/${movie.id}?api_key=${this.apiKey}${this.movieAppend}`
          );
          moviesFullData.push(result.data);
          this.percentageCount += 1;
        }

        return Promise.resolve(moviesFullData);
      } catch (error) {
        console.log(error);
        return Promise.reject(error);
      }
    },
    async submit() {
      this.loading = true;
      let movies = await this.getMoviesFullData();

      this.form
        .transform((data) => ({
          ...data,
          collection: data.collection ? data.collection.id : null,
          manual: false,
          wish_listed: this.wishListed,
          movies,
        }))
        .post(route("movies.store_multi"), {
          preserveScroll: true,
          onSuccess: (result) => {
            this.loading = false;
            this.close("submit");
            this.resetMovies();
            this.getMovies();
          },
          onError: (errors) => {},
        });
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

  emits: ["close", "remove"],

  setup() {
    const { xs } = useDisplay();
    const boutiqueStore = useBoutiqueStore();
    const movieStore = useMovieStore();
    const collectionStore = useCollectionStore();
    const { boutiques } = storeToRefs(boutiqueStore);
    const { baseUrl, apiKey, movieAppend } = storeToRefs(movieStore);
    const { setBoutiqueDialog } = boutiqueStore;
    const { getMovies, resetMovies } = movieStore;
    const { collections } = storeToRefs(collectionStore);
    const { setCollectionDialog, setFilteredCollections } = collectionStore;

    const form = useForm({
      quality: null,
      medium: null,
      boutique: null,
      collection: null,
      manual: false,
      publicly_visible: true,
    });

    return {
      boutiques,
      xs,
      setBoutiqueDialog,
      form,
      baseUrl,
      apiKey,
      movieAppend,
      getMovies,
      collections,
      setCollectionDialog,
      setFilteredCollections,
      collectionStore,
      resetMovies,
    };
  },
};
</script>