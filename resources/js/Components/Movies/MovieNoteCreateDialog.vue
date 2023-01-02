<template>
  <v-dialog v-model="dialog" width="500" persistent>
    <template v-slot:activator="{ props }">
      <v-btn
        class="mb-4"
        v-bind="props"
        prepend-icon="mdi-note-plus-outline"
        variant="tonal"
        >new note</v-btn
      >
    </template>

    <v-card flat rounded="0">
      <v-toolbar flat density="compact">
        <v-toolbar-title>Add Note</v-toolbar-title>
        <v-toolbar-items>
          <v-btn icon="mdi:mdi-close" @click="close"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col>
            <v-text-field label="Title" v-model="form.title" :error-messages="errors.title"></v-text-field>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-select
              v-model="form.type"
              label="Type"
              :items="types"
              item-title="display"
              item-value="value"
              :error-messages="errors.type"
            ></v-select>
          </v-col>
        </v-row>
        <div v-if="form.type == 'external_review'">
          <v-row>
            <v-col>
              <v-text-field label="Byline" v-model="form.byline" :error-messages="errors.byline"></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-text-field
                type="date"
                label="Date"
                v-model="form.date"
                :error-messages="errors.date"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-text-field
                type="text"
                label="External Link"
                v-model="form.external_link"
                :error-messages="errors.external_link"
              ></v-text-field>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
      <v-card-actions>
        <v-btn color="primary" block @click="submit">Submit</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import intus from "intus";
import { isRequired, isIn, isRequiredIf, isUrl } from "intus/rules";

export default {
  name: "MovieNoteCreateDialog",

  data() {
    return {
      dialog: false,
      form: {
        title: null,
        type: null,
        byline: null,
        date: null,
        external_link: null,
      },
      types: [
        { display: "General Message", value: "general_message" },
        { display: "Internal Review", value: "internal_review" },
        { display: "External Review", value: "external_review" },
      ],
      errors: {},
    };
  },

  methods: {
    close() {
      this.dialog = false;
      this.errors = {};
      this.form = {
        title: "",
        type: "",
        byline: "",
        date: "",
        external_link: "",
      };
    },
    submit() {
      const validation = intus.validate(this.form, {
        title: [isRequired()],
        type: [
          isRequired(),
          isIn("general_message", "internal_review", "external_review"),
        ],
        byline: [isRequiredIf("type", "external_review")],
        date: [isRequiredIf("type", "external_review")],
        external_link: [isUrl()],
      });

      if (!validation.passes()) {
        this.errors = validation.errors();
        console.log(validation.errors());
        return;
      }

      let form = {
        ...this.form,
        id: Date.now(),
        text: "",
        date:
          this.form.type == "external_review"
            ? this.form.date
            : new Date().toDateString(),
      };
      this.$emit("submit", form);
      this.close();
    },
  },

  emits: ["submit"],

  setup() {},
};
</script>