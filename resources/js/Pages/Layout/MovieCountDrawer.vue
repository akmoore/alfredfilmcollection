<template>
  <v-navigation-drawer v-model="countDrawer" location="right" temporary>
    <v-list lines="two" nav>
      <div class="text-overline text-grey-darken-2 mb-4 mt-3">
        <v-icon size="x-large" icon="mdi:mdi-counter" class="mb-1 mr-1" />
        Total Count
      </div>

      <div v-for="[key, value] in Object.entries(counts)" :key="key">
        <v-list-item
          :title="key.split('_').splice(0, 2).join(' ').toUpperCase()"
        >
          <template v-slot:prepend>
            <v-avatar
              v-if="key.split('_')[0] != 'total'"
              :color="key.split('_')[1] == 'disc' ? 'purple-accent-3' : 'blue-accent-1'"
            >
              <span class="font-weight-bold">
                {{ key.split("_")[0].toUpperCase() }}
              </span>
            </v-avatar>
          </template>
          <template v-slot:append> <span :class="key.split('_')[0] == 'total' ? 'text-purple-accent-1 text-h4' : ''">{{ value }}</span> </template>
        </v-list-item>
        <v-divider v-if="key.split('_')[0] != 'total'"></v-divider>
      </div>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
export default {
  name: "MovieCountDrawer",

  setup() {
    const store = useMovieStore();
    const { countDrawer, counts } = storeToRefs(store);

    return {
      countDrawer,
      counts,
    };
  },
};
</script>