<template>
  <div id="app" class="bg-light">
    <header class="container-fluid bg-dark text-light">
      <div class="p-4 text-center">
        <h1>Gestionnaire d'erreurs</h1>
      </div>
    </header>
    <keep-alive include="Liste">
      <router-view></router-view>
    </keep-alive>

  </div>
</template>

<script>

  import router from './router';
  export default {
    router,
    name: 'app',
    components: {}
  }
</script>
