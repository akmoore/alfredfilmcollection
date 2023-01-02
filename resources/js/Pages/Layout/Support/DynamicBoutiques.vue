<template>
  <StaticBoutiqueIndividual
    v-for="boutique in boutiques"
    :key="boutique.id"
    @toggle="updateFilter"
    :title="boutique.display"
    :image="boutique.image"
    :type="boutique.id"
    :filter="'boutique'"
  />
</template>

<script>
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import StaticBoutiqueIndividual from "./StaticBoutiqueIndividual.vue";
export default {
  name: "StaticFormats",

  components: {
    StaticBoutiqueIndividual,
  },

  data() {
    return {};
  },

  methods: {},

  setup() {
    const store = useMovieStore();
    const boutiqueStore = useBoutiqueStore();
    const { boutiques } = storeToRefs(boutiqueStore);
    const { filter, movies } = storeToRefs(store);
    const { toggleFilteredItem, updateFilter } = store;

    return {
      store,
      filter,
      toggleFilteredItem,
      updateFilter,
      boutiques,
    };
  },
};
</script>