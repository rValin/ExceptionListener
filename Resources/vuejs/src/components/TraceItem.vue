<template>
    <div v-if="typeof item === 'string'" class="trace-item"><p>{{ item }}</p></div>
    <div v-else class="trace-item">
        <div class="d-flex flex-nowrap" v-on:click="showArgs = !showArgs">
            <p class="flex-fill trace-item-desc">{{ item.file }} in {{ item.function }} at line {{ item.line }}</p>
            <p v-if="typeof item.args == 'object'" class="trace-item-toggle">
                <i v-if="!showArgs" class="fas fa-plus"></i>
                <i v-else class="fas fa-minus"></i>
            </p>
        </div>
        <b-collapse :id="id" v-if="typeof item.args == 'object'" v-model="showArgs" class="trace-details">
            <div class="trace-details-container">
                <p>Args</p>
                <vue-json-pretty :data="item.args" :deep="2" :show-length="true" :highlight-mouseover-node="true">
                </vue-json-pretty>
            </div>
        </b-collapse>
    </div>
</template>

<script>
    import VueJsonPretty from 'vue-json-pretty'

    export default {
        name: 'TraceItem',
        components: {VueJsonPretty},
        data() {
            let randLetter = String.fromCharCode(65 + Math.floor(Math.random() * 26));
            let uniqid = randLetter + Date.now();
            return {
                id: uniqid,
                showArgs: false,
            }
        },
        props: {
            item: [Object, String]
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .trace-item {
        border-top: 1px solid lightgrey;
        padding: 10px;
    }

    .trace-item p{
        margin: 0;
    }

    .trace-item-desc {
        padding-right: 20px;
        word-break: break-all;
    }
    .trace-item-toggle {
        width: 25px;
        min-width: 25px;
        height: 25px;
        line-height: 25px;
        text-align: center;
        align-self: center;
        background-color: #fff;
    }

    .trace-details {
        padding: 15px;
    }

    .trace-details-container {
        background-color: white;
        padding: 15px;
    }
</style>
