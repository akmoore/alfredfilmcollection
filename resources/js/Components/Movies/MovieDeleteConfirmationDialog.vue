<template>
  <v-dialog v-model="deleteConfirmationDialog" persistent width="400">
    <v-card>
      <v-card-text>
        Are you sure you want to delete
        <span class="text-purple-accent-1">{{ activeMovie.name }}</span
        >?
      </v-card-text>
      <v-card-actions>
        <v-btn color="red" variant="plain" @click="close"> No </v-btn>
        <v-spacer></v-spacer>
        <v-btn color="purple-accent-1" variant="plain" @click="deleteMovie">
          Yes
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
export default {
  name: "MovieDeleteConfirmationDialog",

  methods: {
    close() {
      this.setDeleteConfirmationDialog(false);
    },
    async deleteMovie() {
      try {
        await this.deleteSelectedMovie(this.activeMovie);
        this.close();
        this.$emit('movieDeleted')
      } catch (error) {
        return error;
      }
    },
  },

  emits: ["movieDeleted"],

  setup() {
    const store = useMovieStore();
    const { deleteConfirmationDialog, activeMovie } = storeToRefs(store);
    const { setDeleteConfirmationDialog, deleteSelectedMovie } = store;

    return {
      deleteConfirmationDialog,
      setDeleteConfirmationDialog,
      activeMovie,
      deleteSelectedMovie,
    };
  },
};
</script>