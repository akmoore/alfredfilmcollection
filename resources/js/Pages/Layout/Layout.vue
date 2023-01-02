<template>
  <v-app id="kenmovies">
    <!-- ---------------- -->
    <!-- NAVIGATION:START -->
    <!-- ---------------- -->
    <v-navigation-drawer
      v-model="drawer"
      class="d-flex flex-column"
      width="330"
    >
      <v-list v-model:opened="open" class="align-self-start" lines="two">
        <img
          src="alfred_moore_film_collection_logo-dark_gray_a.svg"
          alt="My Happy SVG"
          style="
            width: 80%;
            right: 0px;
            margin-left: 10%;
            margin-bottom: -10px;
            margin-top: -10px;
          "
        />
        <v-list-subheader color="grey-darken-2">
          <v-icon icon="mdi:mdi-filter" />
          Filter
        </v-list-subheader>

        <!-- ------- -->
        <!-- FORMATS -->
        <!-- ------- -->
        <v-list-group :value="0">
          <template v-slot:activator="{ props }">
            <v-list-item v-bind="props" title="Formats"></v-list-item>
          </template>

          <!-- <DynamicFormats /> -->
          <StaticFormats />
        </v-list-group>

        <!-- --------- -->
        <!-- BOUTIQUES -->
        <!-- --------- -->
        <v-list-group :value="1" style="margin-bottom: 3em">
          <template v-slot:activator="{ props }">
            <v-list-item v-bind="props" title="Boutiques"></v-list-item>
          </template>

          <DynamicBoutiques />
        </v-list-group>
      </v-list>

      <!-- ------------- -->
      <!-- AUTHORIZATION -->
      <!-- ------------- -->
      <Auth />
    </v-navigation-drawer>
    <!-- -------------- -->
    <!-- NAVIGATION:END -->
    <!-- -------------- -->

    <!-- ------------------ -->
    <!-- MOVIE COUNT DRAWER -->
    <!-- ------------------ -->
    <MovieCountDrawer />

    <!-- ------------- -->
    <!-- APP-BAR:START -->
    <!-- ------------- -->
    <v-app-bar extended extension-height="80" flat>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title>
        <!-- <img
          src="alfred_moore_film_collection_logo-dark_gray_a.svg"
          alt="My Happy SVG"
          style="width: 5em;"
        />
        The Alfred Film Collection -->
      </v-app-bar-title>

      <v-toolbar-items>
        <MovieSearchDialog v-if="user" />
        <v-btn
          variant="plain"
          color="grey"
          icon="mdi-counter"
          @click="setCountDrawer(true)"
        ></v-btn>
      </v-toolbar-items>
      <template v-slot:extension>
        <v-container>
          <v-row class="mt-4">
            <v-col cols="12">
              <v-text-field
                v-model="term"
                clearable
                label="Search"
                @input="search($event.target.value)"
                @click:clear="clear"
                @keydown.ctrl.enter="clear"
              >
                <template v-slot:append-inner>
                  <v-chip>
                    {{ store.moviesFiltered.length }}
                  </v-chip>
                </template>

                <template v-slot:append>
                  <v-tooltip
                    :text="showWishList ? 'Wish List' : 'Active Catalog'"
                    location="top"
                  >
                    <template v-slot:activator="{ props }">
                      <v-icon
                        :icon="
                          showWishList ? 'mdi-alpha-w-box' : 'mdi-alpha-a-box'
                        "
                        v-bind="props"
                        @click="setWishList(!this.showWishList)"
                      ></v-icon>
                    </template>
                  </v-tooltip>
                </template>
              </v-text-field>
            </v-col>
          </v-row>
        </v-container>
      </template>
    </v-app-bar>
    <!-- ----------- -->
    <!-- APP-BAR:END -->
    <!-- ----------- -->

    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>

<script>
import { useDisplay } from "vuetify";
import { Link } from "@inertiajs/inertia-vue3";
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import { useAuthStore } from "@/Store/AuthStore.js";
import MovieSearchDialog from "../../Components/Movies/MovieSearchDialog.vue";
import Auth from "@/Components/Auth/Auth.vue";
import { debounce } from "lodash";
import DynamicFormats from "@/Pages/Layout/Support/DynamicFormats.vue";
import StaticFormats from "@/Pages/Layout/Support/StaticFormats.vue";
import DynamicBoutiques from "./Support/DynamicBoutiques.vue";
import MovieCountDrawer from "./MovieCountDrawer.vue";

export default {
  components: {
    Link,
    MovieSearchDialog,
    Auth,
    DynamicFormats,
    StaticFormats,
    DynamicBoutiques,
    MovieCountDrawer,
  },

  props: {
    auth_user: Object,
    tmdb_api_key: String,
  },

  created() {
    this.getBoutiques();
    this.setUser(this.auth_user);
    this.setApiKey(this.tmdb_api_key);
    this.getMoviesCount();
  },

  data() {
    return {
      drawer: null,
      countDrawer: true,
      term: null,
      open: [0],
      tabs: 0,
      wishList: false,
    };
  },

  methods: {
    search: debounce(function (value) {
      this.searchLocalMovies(value);
    }, 500),
    clear() {
      this.term = null;
      this.searchLocalMovies("");
    },
    // switched(value, index) {
    //   let filter;
    //   let include;
    //   let array;

    //   if (index == "na") {
    //     filter = value;
    //     include = this.filter[0].includes(filter) ? false : true;
    //     array = 0;
    //   }

    //   if (index != "na") {
    //     filter = `${value.toLowerCase()}_${index == 0 ? "disc" : "digital"}`;
    //     include = this.filter[1].includes(filter) ? false : true;
    //     array = 1;
    //   }

    //   //   console.log(filter, include, this.filter);
    //   this.toggleFilteredItem({ filter, include, array });
    // },
  },

  setup() {
    const store = useMovieStore();
    const { filter, showWishList } = storeToRefs(store);
    const {
      searchLocalMovies,
      toggleFilteredItem,
      setWishList,
      setApiKey,
      setCountDrawer,
      getMoviesCount,
    } = store;
    const { xs } = useDisplay();
    const boutiqueStore = useBoutiqueStore();
    const { boutiques } = storeToRefs(boutiqueStore);
    const { getBoutiques } = boutiqueStore;
    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const { setUser, unsetUser } = authStore;

    return {
      store,
      searchLocalMovies,
      filter,
      toggleFilteredItem,
      xs,
      boutiques,
      getBoutiques,
      user,
      setUser,
      unsetUser,
      setWishList,
      showWishList,
      setApiKey,
      setCountDrawer,
      getMoviesCount,
    };
  },
};
</script>