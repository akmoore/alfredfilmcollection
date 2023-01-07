<template>
  <v-dialog
    v-model="dialog"
    fullscreen
    persistent
    :scrim="false"
    transition="dialog-bottom-transition"
  >
    <template v-slot:activator="{ props }">
      <v-btn density="compact" color="grey-lighten-3" v-bind="props" size="large">
        <v-badge :content="this.list.length" color="purple-accent-1 text-black">
          <v-icon>mdi-note-text-outline</v-icon>
        </v-badge>
      </v-btn>
    </template>

    <v-card>
      <v-toolbar density="compact" flat tiile>
        <v-toolbar-title>
          <v-icon
            size="small"
            color="grey-darken-1"
            icon="mdi:mdi-note-text-outline"
            class="mr-1 mb-1"
          ></v-icon>
          Notes
        </v-toolbar-title>
        <v-toolbar-items>
          <v-btn icon="mdi:mdi-close" @click="close"></v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-card-text>
        <v-row no-gutter>
          <v-col cols="12" md="3" lg="3">
            <MovieNoteCreateDialog v-if="user" @submit="addToList" />
            <v-list theme="light" v-if="list.length">
              <div v-for="(item, index) in list" :key="item.id">
                <v-list-item link :title="item.title" @click="setActive(item)">
                  <template v-slot:prepend v-if="this.activeItem.id == item.id">
                    <v-icon
                      color="purple-darken-2"
                      v-if="this.activeItem.type == 'external_review'"
                      icon="mdi:mdi-alpha-e-circle"
                    ></v-icon>
                    <v-icon
                      color="blue-darken-2"
                      v-if="this.activeItem.type == 'internal_review'"
                      icon="mdi:mdi-alpha-i-circle"
                    ></v-icon>
                    <v-icon
                      color="green-darken-2"
                      v-if="this.activeItem.type == 'general_message'"
                      icon="mdi:mdi-alpha-g-circle"
                    ></v-icon>
                  </template>
                  <template v-slot:append v-if="user">
                    <v-icon
                      size="small"
                      color="error"
                      icon="mdi:mdi-delete"
                      @click="removeItem(item)"
                    ></v-icon>
                  </template>
                </v-list-item>
                <v-divider v-if="index < list.length - 1"></v-divider>
              </div>
            </v-list>
          </v-col>
          <v-col cols="12" md="9" lg="9" class="grey-lighten-3">
            <QuillEditor
              :readOnly="!user"
              v-if="user && activeItem.hasOwnProperty('text')"
              v-model:content="activeItem.text"
              :content="activeItem.text"
              contentType="html"
              style="height: calc(100vh - 150px); font-size: 16px"
              theme="snow"
              :placeholder="user ? 'Start typing here...' : ''"
              toolbar="minimal"
              @textChange="keepSync"
            />
            <v-card
              v-else-if="!user && list.length"
              theme="light"
              style="margin-top: 0px"
              flat
              rounded="0"
            >
              <v-toolbar>
                <v-toolbar-title>
                  <div>
                    <v-icon
                      :color="activeIcon.color"
                      :icon="activeIcon.icon"
                      class="mb-1"
                    ></v-icon>
                    {{ activeItem.title }}
                  </div>
                  <div
                    v-if="
                      activeItem.type == 'external_review' && activeItem.byline
                    "
                    class="text-grey mt-n3"
                    style="margin-left: 1.7em"
                  >
                    <small>
                      {{ activeItem.byline }}
                      <span
                        v-if="xs"
                        style="font-style: italic; font-size: 0.7rem"
                      >
                        , {{ formattedDate }}
                      </span>
                    </small>
                  </div>
                </v-toolbar-title>
                <v-toolbar-items class="d-flex align-center" v-if="!xs">
                  <span class="text-grey-darken-1" style="margin-right: 2em">
                    {{ formattedDate }}
                  </span>
                </v-toolbar-items>
              </v-toolbar>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col>
                      <div
                        style="font-size: 16px"
                        v-html="activeItem.text"
                      ></div>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
            </v-card>
            <div v-else class="text-purple-accent-1">Currently, no notes.</div>
          </v-col>
        </v-row>
        <v-container fluid> </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import "@vueup/vue-quill/dist/vue-quill.bubble.css";
// import MovieNoteCreateDialog from "./MovieNoteCreateDialog.vue";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useAuthStore } from "@/Store/AuthStore.js";
import { storeToRefs } from "pinia";
import { debounce } from "lodash";
import { useDisplay } from "vuetify";
export default {
  name: "MovieNoteDialog",

  props: {
    movie: Object,
  },

  created() {
    this.list = this.movie.notes;
    if (this.movie.notes.length) {
      this.activeItem = this.movie.notes[0];
    }
  },

  components: {
    QuillEditor,
    MovieNoteCreateDialog: defineAsyncComponent(() =>
      import('./MovieNoteCreateDialog.vue')
    ),
  },

  data() {
    return {
      dialog: false,
      list: [],
      activeItem: {},
    };
  },

  computed: {
    activeIcon() {
      if (this.activeItem.hasOwnProperty("type")) {
        return {
          external_review: {
            icon: "mdi-alpha-e-circle",
            color: "purple-darken-2",
          },
          internal_review: {
            icon: "mdi-alpha-i-circle",
            color: "blue-darken-2",
          },
          general_message: {
            icon: "mdi-alpha-g-circle",
            color: "green-darken-2",
          },
        }[this.activeItem.type];
      }
    },
    formattedDate() {
      if (this.activeItem && this.activeItem.type == "external_review") {
        let [year, month, day] = this.activeItem.date.split("-");
        let fullMonth = {
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

        return `${fullMonth} ${day}, ${year}`;
      }

      if (this.activeItem) {
        return this.activeItem.date;
      }

      return null;
    },
  },

  methods: {
    close() {
      this.dialog = false;
      this.getMovies();
    },
    async addToList(item) {
      let result = await this.updateNote(this.movie, item, "add");
      console.log({ result });
      this.list = result;
      this.setActive(item);
    },
    setActive(item) {
      this.activeItem = { ...item, text: item.text ? item.text : " " };
    },
    keepSync: debounce(async function () {
      let newList = this.list.map((li) => {
        if (li.id == this.activeItem.id) {
          return { ...li, text: this.activeItem.text };
        }

        return li;
      });

      this.list = newList;
      await this.updateNote(this.movie, this.activeItem, "update");
    }, 500),
    async removeItem(item) {
      this.activeItem = {};

      let result = await this.updateNote(this.movie, item, "delete");
      this.list = result;

      this.activeItem = this.list[0] ? this.list[0] : {};
    },
  },

  setup() {
    const store = useMovieStore();
    const { updateNote, getMovies } = store;
    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const { xs } = useDisplay();

    return {
      updateNote,
      getMovies,
      user,
      xs,
    };
  },
};
</script>

<style>
/* .ql-container.ql-snow {
  border: none;
} */
.ql-editor.ql-blank::before {
  color: whitesmoke;
}
</style>