<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-purple-50 to-pink-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-primary-600 to-purple-600 bg-clip-text text-transparent mb-2">
          HashMyTag
        </h1>
        <p class="text-gray-600">Connectez-vous à votre compte</p>
      </div>

      <div class="card">
        <form @submit.prevent="login" class="space-y-4">
          <div>
            <label class="label">Email</label>
            <input v-model="form.email" type="email" class="input" placeholder="vous@exemple.com" required autofocus>
          </div>

          <div>
            <label class="label">Mot de passe</label>
            <input v-model="form.password" type="password" class="input" placeholder="••••••••" required>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.remember" type="checkbox" class="w-4 h-4 text-primary-600 rounded">
              <span class="text-sm text-gray-600">Se souvenir de moi</span>
            </label>
            <a href="#" class="text-sm text-primary-600 hover:text-primary-700">
              Mot de passe oublié ?
            </a>
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>

        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-300"></div>
          <span class="px-4 text-sm text-gray-500">Ou continuer avec</span>
          <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
          <a href="/auth/facebook" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <span class="text-xl">👍</span>
            Facebook
          </a>
          <a href="/auth/google" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <span class="text-xl">🔍</span>
            Google
          </a>
          <a href="/auth/twitter" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <span class="text-xl">🐦</span>
            Twitter
          </a>
          <a href="/auth/instagram" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <span class="text-xl">📷</span>
            Instagram
          </a>
        </div>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Pas encore de compte ?
            <Link href="/register" class="text-primary-600 font-medium hover:text-primary-700">
              Créer un compte
            </Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const form = ref({
  email: '',
  password: '',
  remember: false
});

const loading = ref(false);

const login = () => {
  loading.value = true;
  router.post('/login', form.value, {
    onFinish: () => {
      loading.value = false;
    }
  });
};
</script>

