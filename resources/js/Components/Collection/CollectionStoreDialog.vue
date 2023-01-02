<template>
  <v-dialog v-model="collectionDialog" width="500">
    <v-card>
      <v-toolbar density="compact">
        <v-toolbar-title>Create Collection</v-toolbar-title>
        <v-toolbar-items>
          <v-btn @click="close">
            <v-icon icon="mdi:mdi-close"></v-icon>
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col>
              <v-text-field
                v-model="form.name"
                label="Name"
                :error-messages="errors.name"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-autocomplete
                v-model="form.boutique"
                label="Boutique"
                :items="boutiques"
                item-title="display"
                item-value="id"
                :error-messages="errors.boutique"
              ></v-autocomplete>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-btn color="primary" block @click="submit"> Submit </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import intus from "intus";
import { isRequired, isNumeric, isMin } from "intus/rules";
import { storeToRefs } from "pinia";
// import { useForm } from "@inertiajs/inertia-vue3";
import { useCollectionStore } from "@/Store/CollectionStore.js";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
import axios from 'axios';

export default {
  name: "CollectionStoreDialog",

  props: {
    boutique: Number,
  },

  data() {
    return {
      form: {
        name: null,
        boutique: null,
      },
      errors: {}
    };
  },

  watch: {
    boutique(newVal) {
      this.form.boutique = newVal;
    },
  },

  methods: {
    close() {
      this.setCollectionDialog(false);
      this.resetForm();
    },
    async submit() {
        try {
            const validate = intus.validate(this.form, {
                name: [isRequired(), isMin(3)],
                boutique: [isNumeric()]
            })

            if(!validate.passes()){
                this.errors = validate.errors();
                return;
            }

            const result = await axios.post(route('collections.store'), this.form);
            this.close()
            this.getCollections();
            this.$emit("close", result.data)
        } catch (error) {
            console.log(error)
        }
      //   this.form.post(route("collections.store"), {
      //     preserveScroll: true,
      //     onSuccess: () => {
      //         this.form.reset(["name", "boutique"])
      //     },
      //   });
    },
    resetForm(){
        this.form = {
            name: null,
            boutique: null,
        }
    }
  },

  emits: ["close"],

  setup() {
    const store = useCollectionStore();
    const { collectionDialog } = storeToRefs(store);
    const { setCollectionDialog, getCollections } = store;
    const boutiqueStore = useBoutiqueStore();
    const { boutiques } = storeToRefs(boutiqueStore);

    // const form = useForm({
    //   name: null,
    //   boutique: null,
    // });

    return {
      setCollectionDialog,
      collectionDialog,
      boutiques,
      getCollections,
      //   form,
    };
  },
};
</script>