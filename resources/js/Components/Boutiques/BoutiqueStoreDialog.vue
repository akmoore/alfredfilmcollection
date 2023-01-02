<template>
  <v-dialog v-model="boutiqueDialog" persistent width="400">
    <v-card>
      <v-toolbar density="compact" title="Add Boutique"> </v-toolbar>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col>
              <v-text-field
                clearable
                label="Distributor"
                v-model="form.boutique"
                :hide-details="!errors.boutique"
                :error-messages="errors.boutique"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-file-input
                clearable
                label="Image"
                v-model="form.image"
                :hide-details="!errors['image.0']"
                :error-messages="errors['image.0']"
              ></v-file-input>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-btn
          variant="plain"
          color="error"
          @click="setBoutiqueDialog(false)"
          class="ml-4 mb-2"
        >
          Cancel
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn variant="plain" color="success" class="mr-4 mb-2" @click="save">
          Save
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import intus from "intus";
import { isRequired, isImage } from "intus/rules";
import { storeToRefs } from "pinia";
import { useBoutiqueStore } from "@/Store/BoutiqueStore.js";
export default {
  name: "BoutiqueStoreDialog",

  data() {
    return {
      boutique: null,
      form: {
        boutique: null,
        image: [],
      },
      errors: {},
    };
  },

  methods: {
    cancel() {
      this.setBoutiqueDialog(false);
      this.boutique = null;
      this.form = {
        boutique: null,
        image: [],
      }
    },

    save() {
      this.errors = {};
      const validate = intus.validate(this.form, {
        boutique: [isRequired()],
        "image.0": [isImage()]
      },{'image.0': 'File'});

      if (!validate.passes()) {
        this.errors = validate.errors();
        console.log(this.errors)
        return;
      }

      let boutique = this.storeBoutique(this.form);
      this.$emit("boutique", boutique);
      this.cancel();
    },
  },

  emits: ["boutique"],

  setup() {
    const store = useBoutiqueStore();
    const { boutiqueDialog } = storeToRefs(store);
    const { setBoutiqueDialog, storeBoutique } = store;

    return {
      boutiqueDialog,
      setBoutiqueDialog,
      storeBoutique,
    };
  },
};
</script>