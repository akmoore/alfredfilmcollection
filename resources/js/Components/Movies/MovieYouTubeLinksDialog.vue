<template>
  <v-dialog
    v-model="dialog"
    persistent
    width="1000"
    origin="center center"
    :scrim="!xs"
    :transition="xs ? 'dialog-bottom-transition' : 'dialog-transition'"
    :fullscreen="xs ? true : false"
  >
    <template v-slot:activator="{ props }">
      <v-btn
        size="large"
        variant="plain"
        v-bind="props"
        style="mix-blend-mode: difference"
      >
        <v-icon size="x-large" icon="mdi-youtube"></v-icon>
      </v-btn>
    </template>

    <v-card>
      <v-toolbar
        flat
        density="compact"
        style="position: fixed; width: 100%; z-index: 10"
      >
        <v-toolbar-title>
            <v-icon icon="mdi:mdi-youtube"></v-icon>
            Videos
        </v-toolbar-title>
        <v-toolbar-items>
          <v-btn icon="mdi:mdi-close" @click="dialog = false"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text v-if="currentVideos.length" style="margin-top: 50px">
        <v-carousel
          @update:modelValue="onCarouselChange($event)"
          :show-arrows="false"
          height="600"
          v-if="!xs"
        >
          <v-carousel-item v-for="(video, index) in videos" :key="video.id">
            <YouTube
              width="100%"
              height="600"
              :src="`https://www.youtube.com/watch?v=${video.key}`"
              :ref="`youtube${index}`"
            />
          </v-carousel-item>
        </v-carousel>

        <div v-else>
          <v-row v-for="video in videos" :key="video.id">
            <v-col>
              <iframe
                id="ytplayer"
                type="text/html"
                width="100%"
                height="240"
                :src="`https://www.youtube.com/embed/${video.key}`"
                frameborder="0"
              ></iframe>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
      <v-card-actions v-if="!xs">
        <v-btn color="purple" block @click="dialog = false"> Close </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import YouTube from "vue3-youtube";
import { useDisplay } from "vuetify";
export default {
  name: "MovieYouTubeLinksDialog",

  components: {
    YouTube,
  },

  props: {
    videos: {
      type: Array,
      required: true,
    },
  },

  data() {
    return {
      dialog: false,
      currentVideo: 0,
    };
  },

  computed: {
    currentVideos() {
      if (this.videos) {
        return this.videos;
      }

      return [];
    },
  },

  methods: {
    onCarouselChange(event) {
      console.log(this.$refs);
      if (
        this.$refs[`youtube${this.currentVideo}`] &&
        this.$refs[`youtube${this.currentVideo}`][0] &&
        this.$refs[`youtube${this.currentVideo}`][0].getPlayerState() == 1
      ) {
        this.$refs[`youtube${this.currentVideo}`][0].stopVideo();
      }

      this.currentVideo = event;
    },
  },

  setup() {
    const { xs } = useDisplay();

    return {
      xs,
    };
  },
};
</script>