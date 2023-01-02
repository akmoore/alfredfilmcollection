<template>
  <!-- ---- -->
  <!-- LIST -->
  <!-- ---- -->
  <v-list style="position: absolute; bottom: 0; width: 100%;padding-bottom: 0px;">
    <v-list-item @click="dialog = true" v-if="!user" style="background: #121212;">
      <v-list-item-title>Login</v-list-item-title>
      <template v-slot:prepend>
        <v-icon color="purple-accent-1" icon="mdi:mdi-lock"></v-icon>
      </template>
    </v-list-item>

    <v-list-item @click="logout" v-else style="background: #121212;">
      <v-list-item-title>Logout</v-list-item-title>
      <template v-slot:prepend>
        <v-icon color="grey-darken-2" icon="mdi:mdi-lock-open"></v-icon>
      </template>
    </v-list-item>
  </v-list>

  <!-- ------ -->
  <!-- DIALOG -->
  <!-- ------ -->
  <v-dialog v-model="dialog" width="400" persistent>
    <v-card>
      <v-toolbar density="compact" tile flat>
        <v-toolbar-title>Login</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn icon="mdi:mdi-close" @click="close"></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col>
              <v-text-field
                v-model="form.email"
                label="Email"
                :error-messages="form.errors.email"
                autofocus
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-text-field
                v-model="form.password"
                label="Password"
                type="password"
                :error-messages="form.errors.password"
                @keydown.enter="login"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-btn :disabled="form.processing" color="primary" block @click="login">
          Login
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/Store/AuthStore.js";
import { useForm } from "@inertiajs/inertia-vue3";
import { useMovieStore } from "@/Store/MovieStore.js";
export default {
  name: "Auth",

  data() {
    return {
      dialog: false,
    };
  },

  methods: {
    close() {
      this.dialog = false;
    },
    login() {
      this.form.post(route("login"), {
        preserveScroll: true,
        onSuccess: (result) => {
          this.dialog = false;
          this.form.reset("password", "email", "remember");
          this.setUser(result.props.auth_user);
          this.resetMovies();
          this.getMovies();
        },
      });
    },
  },

  setup() {
    const store = useAuthStore();
    const movieStore = useMovieStore();
    const { user } = storeToRefs(store);
    const { logout, setUser } = store;
    const { getMovies, resetMovies } = movieStore;

    const form = useForm({
      email: null,
      password: null,
      remember: false,
    });

    return {
      form,
      user,
      logout,
      setUser,
      getMovies,
      resetMovies,
    };
  },
};
</script>