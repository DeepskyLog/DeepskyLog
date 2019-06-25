<style scoped>
select {
    width: 100%;
}
</style>

<template>
    <div>
        <select>
            <slot></slot>
        </select>
    </div>
</template>

<script type="text/javascript">

export default {
    props: ['options', 'data-url', 'value'],
            watch: {
                value: function (value) {
                // update value
                $(this.$el)
                    .val(value)
                    .trigger('change')
                },
                options: function (options) {
                    // update options
                    $(this.$el).empty().select2({ data: options })
                }
            },

    mounted: function () {
      var vm = this
        alert("TEST1");
      $(this.$el)
        // init select2
        .select2({ data: this.options, theme: 'bootstrap', width: '100%', allowClear: true })
        .val(this.value)
        .trigger('change')
        // emit event on change.
        .on('select2:select', function () {
          vm.$emit('input', this.value)
        })
        alert("TEST2");
            // Populate the list via ajax if "data-url" prop has been defined.
            if (this.dataUrl !== undefined) {
                var self = this;
                this.$http.get(url)
                    .then(response => {
                        self.$set('list', JSON.parse(response.data));
                    });
                //this.getList(this.dataUrl);
            }

    },
    destroyed: function () {
      $(this.$el).off().select2('destroy')
    },
    data() {
        return {
            list: []
        }
    }
}
</script>
