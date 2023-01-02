<template>
  <template
    v-for="([key, value], index) in Object.entries(store.formats)"
    :key="index"
  >
    <template v-for="(v, i) in value" :key="i">
      <v-list-item>
        <v-list-item-title>
          {{ `${key}` }}
        </v-list-item-title>

        <v-list-item-subtitle>
          <span
            :class="i == 0 ? 'text-purple-accent-1' : 'text-blue-lighten-3'"
          >
            {{ i == 0 ? "Disc" : "Digital" }}
          </span>
          <v-chip v-if="v" size="x-small" variant="outlined" label class="ml-1">
            {{ v }}
          </v-chip>
        </v-list-item-subtitle>

        <template v-slot:append>
          <v-switch
            flat
            inset
            density="compact"
            :color="i == 0 ? 'purple-accent-1' : 'blue-lighten-3'"
            hide-details
            @change="switched(key, i)"
          ></v-switch>
        </template>
      </v-list-item>
      <v-divider inset></v-divider>
    </template>
  </template>
</template>

<script>
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
export default {
  name: "DynamicFormats",

  methods: {
    switched(value, index) {
      let filter;
      let include;
      let array;

      if (index == "na") {
        filter = value;
        include = this.filter[0].includes(filter) ? false : true;
        array = 0;
      }

      if (index != "na") {
        filter = `${value.toLowerCase()}_${index == 0 ? "disc" : "digital"}`;
        include = this.filter[1].includes(filter) ? false : true;
        array = 1;
      }

      this.toggleFilteredItem({ filter, include, array });
    },
  },

  setup() {
    const store = useMovieStore();
    const { filter } = storeToRefs(store);
    const { toggleFilteredItem } = store;

    return {
      store,
      filter,
      toggleFilteredItem,
    };
  },
};
</script>