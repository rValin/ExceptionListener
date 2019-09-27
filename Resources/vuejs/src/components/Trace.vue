<template>
    <div class="trace p-3">
        <b-card no-body>
            <b-tabs pills vertical nav-wrapper-class="col-4">
                <b-tab v-for="item in trace" :title="item.file +' in '+ item.function +' at line '+item.line">
                    <b-card-text class="p-2">
                        <div class="code" v-if="item.lines">
                            <p class="exception-section-title">Code</p>
                            <div v-for="(line, key) in item.lines">
                                <span class="line-number">{{ key }}</span>
                                <span class="line-code">{{ line }}</span>
                            </div>
                        </div>
                        <hr v-if="item.args && item.lines" />
                        <div class="trace-details-container" v-if="item.args">
                            <p class="exception-section-title">Args</p>
                            <vue-json-pretty :data="item.args" :deep="2" :show-length="true" :highlight-mouseover-node="true">
                            </vue-json-pretty>
                        </div>
                    </b-card-text>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import VueJsonPretty from 'vue-json-pretty'
    export default {
        name: 'Trace',
        components: {VueJsonPretty},
        props: {
            trace: Array
        },
        data() {
            return {
                currentItem: this.trace[0],
            };
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .trace {
        margin-bottom: 15px;
        word-break: break-all;
    }

    .exception-section-title {
        font-weight: bold;
    }
</style>
