<template>
    <div class="data-item">
        <span class="data-item-name">{{ name }}</span>
        <span class="data-item-value">{{ value}}</span>
    </div>
</template>

<script>
    export default {
        name: 'DataItem',
        props: {
            name: String,
            value: [String, Number],
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .data-item {
        border: 1px solid rgb(189, 180, 199);
        border-radius: 4px;
        overflow: hidden;
        display: flex;
        margin: 15px;
        font-size: .8em;
    }

    .data-item-value, .data-item-name {
        padding: 5px;
        min-width: 45px;
    }

    .data-item-value {
        border-left: 1px solid rgb(189, 180, 199);
        background-color: rgb(251, 251, 252);
    }
</style>
