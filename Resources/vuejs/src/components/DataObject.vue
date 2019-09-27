<template>
    <div class="data-object">
        <h3>{{ title }}</h3>
        <div class="data-object-list">
            <div class="data-object-item row" v-for="(value, key) in values">
                <div class="col-3">{{ key }}</div>
                <div class="col-8 data-object-item-value" v-if="typeof value !== 'object'">{{ value }}</div>
                <div class="col-9 data-object-item-value" v-else>
                    <vue-json-pretty :data="value" :deep="2" :show-length="true" :highlight-mouseover-node="true"></vue-json-pretty>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueJsonPretty from 'vue-json-pretty';

    export default {
        name: 'DataObject',
        components: {VueJsonPretty},
        props: {
            title: String,
            values: [Array, Object],
        },
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->

<style scoped>
    .data-object {
        border-top: 1px solid rgba(0, 0, 0, 0.125);
        padding: 15px;
    }

    .data-object-item {
        margin-bottom: 15px;
    }

    .data-object-item-value {
        background-color: rgb(251, 251, 252);
        padding: 10px 15px;
        word-break: break-all;
    }
</style>
