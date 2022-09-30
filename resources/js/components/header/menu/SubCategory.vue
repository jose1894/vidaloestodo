<template>
    <ul class="sub-category" >
        <li v-for="subitem in subcategory" :key="subitem.id">
            <a href="#">
                {{subitem.name}}
            </a>

            <div class="cate-icon" v-if="subitem.all_subcategories.length > 0">
                <i class="las la-angle-down"></i>
            </div>
            <menu-subcategory v-if="subitem.all_subcategories.length > 0" :subcategory="subitem.all_subcategories"></menu-subcategory>
        </li>
    </ul>    
                   
</template>

<script>
    export default {
        props: ["subcategory"],
        mounted() {
           // console.log(this.category)
        }
    }
</script>

