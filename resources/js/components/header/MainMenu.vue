<template>
    <div class="menu-argo-sidebar-area" id="menu-argo-sidebar-area">
        <span class="side-sidebar-close-btn"><i class="las la-times"></i></span>
        <div class="tab-pane" id="cate">
            <div class="left-category xargo-sub">
                <menu-category :category="this.main"></menu-category>                
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ["main"],
        mounted() {
            
            $(".categories li .cate-icon").on("click", function (i) {                
                var a = $(this.$el).parent("li");
                a.hasClass("open")
                ? (a.removeClass("open"), a.children("ul").slideUp(400, "swing"))
                : (a.addClass("open"),
                a.siblings("li").removeClass("open"),
                a.children("ul").slideDown(400, "swing"));
            });
        }
    }
</script>

