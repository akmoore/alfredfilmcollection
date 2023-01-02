<template>
  <v-dialog
    v-model="dialog"
    width="700"
    persistent
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
  >
    <template v-slot:activator="{ props: dialogprops }">
      <v-tooltip text="Manually Add Film" location="top">
        <template v-slot:activator="{ props: tooltip }">
          <v-btn
            color="grey-lighten-1"
            v-bind="{ ...dialogprops, ...tooltip }"
            variant="plain"
            icon="mdi:mdi-plus-box"
          ></v-btn>
        </template>
      </v-tooltip>
    </template>

    <v-card>
      <v-toolbar flat tile density="compact">
        <v-toolbar-title>Manually Add Film</v-toolbar-title>
        <v-toolbar-items>
          <v-btn @click="close" icon="mdi:mdi-close"> </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text>
        <v-container fluid>
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
              <v-text-field
                label="Name"
                v-model="form.name"
                :error-messages="form.errors.name"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-file-input
                v-model="form.poster"
                clearable
                label="Poster"
                :error-messages="form.errors.poster"
              ></v-file-input>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.certification"
                clearable
                label="Certification"
                :items="certifications"
                :error-messages="form.errors.certification"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                type="number"
                v-model="form.runtime"
                clearable
                label="Runtime"
                hint="Measured in minutes."
                :error-messages="form.errors.runtime"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.quality"
                label="Quality"
                :items="qualities"
                item-title="display"
                item-value="value"
                :error-messages="form.errors.quality"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.medium"
                label="Medium"
                :items="mediums"
                item-title="display"
                item-value="value"
                :error-messages="form.errors.medium"
              ></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.release_date"
                label="Release Date"
                type="date"
                :error-messages="form.errors.release_date"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-autocomplete
                clearable
                chips
                multiple
                v-model="form.genres"
                label="Genres"
                :items="genres"
                item-title="name"
                item-value="id"
                return-object
                :error-messages="form.errors.genres"
              ></v-autocomplete>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-textarea
                label="Tagline"
                clearable
                auto-grow
                bg-color="grey-darken-3"
                rows="1"
                v-model="form.tagline"
                :error-messages="form.errors.tagline"
              ></v-textarea>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-textarea
                label="Description"
                clearable
                auto-grow
                rows="3"
                bg-color="grey-darken-3"
                v-model="form.description"
                :error-messages="form.errors.description"
              ></v-textarea>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-btn color="purple-accent-1" block @click="submit">Submit</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { useMovieStore } from "@/Store/MovieStore.js";
import genres from "./Support/genres";
import { useForm } from "@inertiajs/inertia-vue3";
import { useDisplay } from "vuetify";

export default {
  name: "MovieManualAddDialog",

  props: {
    wishListed: Boolean,
  },

  data() {
    return {
      dialog: false,
      certifications: ["G", "PG", "PG-13", "R", "NC-17", "NR"],
      qualities: [
        { display: "4K", value: 2160 },
        { display: "HD 1080p", value: 1080 },
        { display: "HD 720p", value: 720 },
        { display: "HD 540p", value: 540 },
        { display: "SD", value: 480 },
      ],
      mediums: [
        { display: "Disc", value: "disc" },
        { display: "Digital", value: "digital" },
      ],
      genres,
    };
  },

  methods: {
    close() {
      this.form.reset(
        '"name"',
        "poster",
        "runtime",
        "certification",
        "quality",
        "medium",
        "tagline",
        "description",
        "manual",
        "release_date",
        "genres",
        "publicly_visible"
      );
      this.form.clearErrors();
      this.dialog = false;
    },
    submit() {
      this.form
        .transform((data) => ({
          ...data,
          poster: data.poster.length ? data.poster[0] : null,
          wish_listed: this.wishListed,
        }))
        .post(route("movies.store_manual"), {
          preserveScroll: true,
          onSuccess: (result) => {
            this.close();
            this.store.resetMovies();
            this.store.getMovies();
            //   this.form.reset("password", "email", "remember");
            //   this.setUser(result.props.auth_user);
          },
        });
    },
  },

  setup() {
    const store = useMovieStore();
    const { xs } = useDisplay();

    const form = useForm({
      name: null,
      poster: [],
      runtime: 0,
      certification: null,
      quality: null,
      medium: null,
      tagline: null,
      description: null,
      manual: true,
      release_date: null,
      genres: null,
      publicly_visible: true,
    });

    return {
      form,
      store,
      xs,
    };
  },
};
</script>