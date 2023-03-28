<template>
    <ul class="categories">
        <li v-for="item in category" :key="item.id">
            <a :href="route(item)">
                {{ item.name }}
            </a>

            <div class="cate-icon" v-if="item.all_subcategories.length > 0">
                <i class="las la-angle-down"></i>
            </div>

            <menu-subcategory
                v-if="item.all_subcategories.length > 0"
                :subcategory="item.all_subcategories"
            ></menu-subcategory>
        </li>
    </ul>
</template>

<script>
export default {
    props: ["category"],
    mounted() {
        //console.log(routerCategory)
    },
    methods: {
        route: function(item) {
            //  console.log(item);
            axios
                .get(homeUrl + "/define_route", {
                    params: {
                        id: item.id,
                        slug: item.name
                    }
                })
                .then(function(res) {
                    //console.log(res);
                    return res
                })
                .catch(function(err) {
                    console.log("err", err);
                });
        }
    }
};
</script>
