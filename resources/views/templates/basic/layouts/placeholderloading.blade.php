<div class="card-body" id="dynamic_content">
    <div class="row" >
        <div class="col-12 col-sm-12">
            <div class="ph-item-banner">
                <div class="ph-col-12">
                    <div class="ph-picture"></div>            
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-left: 1px;margin-right: 0px;">
        <div class="col-12 col-sm-4">
            <div class="ph-item-banner">
                <div class="ph-col-12">
                    <div class="ph-picture-product"></div>            
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="ph-item-banner">
                <div class="ph-col-12">
                    <div class="ph-picture-product"></div>            
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="ph-item-banner">
                <div class="ph-col-12">
                    <div class="ph-picture-product"></div>            
                </div>
            </div>
        </div>
    </div>        
    <div class="row" style="margin-left: 1px;margin-right: 0px;" id="placeholder_product">
        @for ($count=0; $count < 15; $count++)
            @for ($count1 =0; $count1 < 6; $count1++)
            <div class="col-6 col-sm-2">

                <div class="ph-item">
                    <div class="ph-col-12">
                        <div class="ph-picture"></div>
                        <div class="ph-row">
                            <div class="ph-col-4"></div>
                            <div class="ph-col-8 empty"></div>
                            <div class="ph-col-12"></div>
                        </div>
                    </div>
                    <div class="ph-col-2">
                        <div class="ph-avatar"></div>
                    </div>
                    <div>
                        <div class="ph-row">
                            <div class="ph-col-12"></div>
                            <div class="ph-col-2"></div>
                            <div class="ph-col-10 empty"></div>
                            <div class="ph-col-8 big"></div>
                            <div class="ph-col-4 big empty"></div>
                        </div>
                    </div>
                </div>

            </div>
            @endfor 
        @endfor    
    </div>
</div>