<template>
    <div>
        <h2>Occurence du {{ occurence.exception_date }}</h2>
        <div class="data-container">
            <data-item name="Utilisateur" :value="occurence.user"></data-item>
            <data-item name="app_version" :value="occurence.app_version"></data-item>
        </div>
        <div>
            <h3>Détails</h3>
            <trace :trace="occurence.trace"></trace>

            <data-object v-if="!isEmptyObject(occurence.extra.POST)" :values="occurence.extra.POST" title="Post"></data-object>
            <data-object v-if="!isEmptyObject(occurence.extra.COOKIES)" :values="occurence.extra.COOKIES" title="Cookies"></data-object>
            <data-object v-if="!isEmptyObject(occurence.extra.SESSION)" :values="occurence.extra.SESSION" title="Session"></data-object>
        </div>
    </div>
</template>

<script>
    import Trace from "./Trace";
    import DataItem from "./DataItem";
    import DataObject from "./DataObject";
    export default {
        name: 'Occurence',
        components: {DataObject, DataItem, Trace},
        props: {
            occurence: Object,
        },
        methods: {
            isEmptyObject(value) {
                if (!value || typeof value !== 'object') {
                    return true;
                }

                return JSON.stringify({}) === JSON.stringify(value) || JSON.stringify([]) === JSON.stringify(value);
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .data-container {
        display: flex;
        flex-wrap: wrap;
    }
</style>
