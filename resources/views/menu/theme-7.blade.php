@extends('layouts.app')
@section('head-scripts')
<link rel="stylesheet" href="{{asset('css/theme-7.css'). '?_=' . config('app.version_date')}}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.0/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.min.js"></script>
@include('layouts.main-color-style', ['color' => $restaurant->main_theme_color_code??'#8140a8'])
<script>
    function phone_code() {
        return '<?php echo $restaurant->phone_code ?>';
    }
</script>
<style>

    @if($restaurant->enable_component > 0)
        .btn-open_details 
        {
            display:none !important;
        }
    @endif
</style>
@endsection
@section('body-content')
<div id="app_main" >
<div id="app" v-cloak>
    <div class="rating" >
           @include('layouts.rating')
    </div>
         
    <div class="d-flex-center">
        <div class="col-lg-6 border no-border-color col-sm-12 col-md-12 items-container" >
            <div class="position-relative back-images" >
                <!-- <i class="language"> @include('layouts.lang') </i> -->
                @if($restaurant->is_logo_active == 1)
                <div class="logo">
                    <img loading="lazy" src="{{$restaurant->logoUrl()}}" alt="" width="100" height="100" class="shadow" style="">
                </div>
                @endif
                <div class="d-flex" >
                    @foreach(json_decode($restaurant->backgroundImageUrl()) as $index => $info)                
                                    @if($index == 0)
                                        <img loading="lazy"  style="display: block;" src="{{$info}}" class="slide" alt="{{$restaurant->name}}">
                                    @else
                                        <img loading="lazy" style="display: none;" src="{{$info}}" class="slide" alt="{{$restaurant->name}}"> 
                                    @endif
                    @endforeach
                </div>    
            </div>
            <br>
            <div class="text-center mx-2">

                <div class="restaurant-title" >
                    @foreach(explode('/', $restaurant->name) as $info)
                    <label class="shop_name" >{{$info}}</label>
                    <br>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center">

                </div>
                @include('layouts.menu-partials.working-hours')
                <div class="text-center">
                    @include('layouts.menu-partials.notes')
                </div>
                <div class="text-center">
                    @include('layouts.menu-partials.contacts')
                </div>


            </div>

            <!-- shwowing categories -->

            <div class="box-categories collapse">

                @foreach($restaurant->categories as $category)
                @foreach(explode('---', $category->name ) as $index => $info)
                @if($info == $lang)


                @foreach(explode('---', $category->name ) as $index => $info)
                @if($index == 0)
                <a class="px-2 py-2  cursor-pointer flatted-well" @click="chooseActiveCategory({{$category->id}})" id="{{$category->id}}">
                {{$info}}
                </a>

                @endif
                @endforeach
                @endif
                @endforeach
                @endforeach

            </div>


            <!-- showing categories ended  -->


        </div>
         
    </div>
    <div class="main-content px-2">
        <div class="container-fluid  justify-content-center  flex-column" id="items-container" v-cloak>
            @foreach($restaurant->categories->sortBy('priority') as $category)

            @foreach(explode('---', $category->name ) as $index => $info)
            @if($info == $lang)
            <div class="col-lg-12 col-md-12 col-sm-12 py-2" >
                <button class="width-100 d-flex justify-content-space-between align-items-center box-categories" onclick="toggleItems(document.getElementById('item_{{$category->id}}'))" >
                    {{explode('---', $category->name )[0]}}
                    <i class="fa fa-plus" ></i>
                </button>
            </div>
            <div class="mt-4 justify-content-center item items-main text-center"  id="item_{{$category->id}}">
                <div class="items-container">
                    @foreach($category->items->sortBy('priority') as $item)
                    @if($item->is_visible)
                    <div  class="position-relative bg-white my-2 border-radius-10 {'{{(!$item->is_available) ? '': ''}}': true, 'added': typeof cart[{{$item->id}}] !== 'undefined'}">
                        <div class="item-wrapper position-relative d-flex">
                            <div class="align-items-center item-image-box">
                                <img  src="{{$item->image_path}}" alt="{{$item->name}}"  class="item-image" aria-view="true">
                            </div>
                                <div class="d-flex justify-content-space-between width-100 py-2" >
                                <div>
                                    <div class="product-title-container" >
                                            @foreach(explode('/', $item->name) as $info)
                                            <label  class="product-title {{strlen($item->name)>20? 'extensable cursor-pointer': ''}}" {{strlen($item->name)>20? 'onclick=expandTitle(event)': ''}}>
                                                {{$info}}
                                            </label>
                                            @endforeach
                                    </div>
                                    <div class="description" >
                                            @foreach(explode('/', $item->description) as $info)
                                                {{$info}}
                                                <br>
                                            @endforeach
                                    </div>  
                                    <div class="text-right" style="font-size:14px" >
                                        @if($item->calories)
                                                <span >{{$item->calories}}</span>
                                                <img src="/public/images/calories.png" width="5" height="10" alt="">    

                                                @endif    
                                    </div>  
                                </div>

                                <div class="price">
                                            @if($item->current_price > 0)
                                            <span class=" price-tag">{{$item->current_price}} {{$restaurant->currency}}</span>
                                            @endif
                                            @if($item->old_price > $item->current_price && $item->old_price > 0 )
                                            <span class="strike text-danger ">{{$item->old_price}} {{$restaurant->currency}}</span>
                                            @endif
                                            
                                        </div>
                                </div>
                           <div>
                           <div class="btn-sub-details" >
                                    @include('layouts.menu-partials.sub_details')
                                </div> 
                                <div id="add_to_cart_avl{{$item->id}}" class="add-to-cart-ccontainer" >
                               
                                @if($restaurant->enable_component > 0 && $item->is_available)

                                @if($item->quantity_summary && $item->quantity_summary['total'] !=null && $item->quantity_summary['remaining'] == 0)
                                    <div class="fix-at-bottom item-out-of-stock-for-consumer text-center" >
                                            <label for="">
                                            <b>
                                                نفذت الكمية
                                            </b>
                                            </label>
                                    </div> 
                                @else 

                                <div class="fix-at-bottom text-white btn-add-to-cart place-order d-flex justify-content-left align-items-center" >
                                    
                                    <button class="active cursor-pointer px-2 nb-no"  v-if="typeof cart[{{$item->id}}] === 'undefined'" @click="addToCart({{$item}} , getSubDetailsData({{$item->id}})) "> 
                                        <i class="fa fa-plus" > </i>
                                    </button>
                                    <button class="active cursor-pointer nb-no" v-if="typeof cart[{{$item->id}}] !== 'undefined'" @click="removeFromCart({{$item->id}})">                                 
                                        <i class="btn-remove-to-cart fa fa-minus" > </i>    
                                    </button>
                                </div>
                                @endif
                                @endif
                                </div>
                           </div>         
                            <br>
                        </div>
                        <div class="container" >
                                     @include('layouts.menu-partials.allergens')
                                </div>     
                    </div>
                    @endif

                    @endforeach

                    @if(!count($category->items))
                    <p>ﻻ توجد منتجات حالياً</p>
                    @endif
                </div>
            </div>
            @endif
            @endforeach

            @endforeach
        </div>
        @include('layouts.menu-partials.made-with-love')

        @if($restaurant->enable_component > 0)
        <div class="d-flex justify-content-around align-items-center" id="order-section">
            <button  :class="{'flatted-well cursor-pointer w-100-sm w-50': true, active: cartItemsCount, 'not-allowed' :cartItemsCount===0 }" id="place-order" data-toggle="modal" data-target="#place-order-modal" :disabled="cartItemsCount===0">
                <span  class="items-count">@{{cartItemsCount}}</span>

                </span>

                <i class="fs-12 fas fa-shopping-cart ml-2 btn-shopping-cart"></i>
            </button>
        </div>
        @include('layouts.menu-partials.place-order-modal')
        @endif
    </div>
    @include('layouts.menu-partials.floating-alert')
</div>
</div>    
@endsection

@section('body-scripts')
<script src="{{asset('js/menu.js')}}"></script>

<script>
    function expandTitle(event) {
        $(event.target).removeClass('extensable').removeClass('cursor-pointer').closest('div.item-wrapper').first().addClass('expanded scrollable-hidden-bar');
    }
</script>
@include('layouts.image-viewer')

<script>

    // showing category items started
    var childcounter = 0;
    var MAIN_THEME_COLOR = "{{$restaurant->main_theme_color_code??'#8140a8'}}";
    $(function() {
        /* close all items */
        $(".items-main").hide();
        // setting back theme elements
        setBackElementsThisTheme();
        // code about cateogries box
        var tag = $(".box-categories").children().eq(childcounter);
        var attr_id = tag.attr('id');
        tag.addClass("category-selected");
        tag.css({"background-color" : MAIN_THEME_COLOR});
        $("#a" + attr_id).removeClass('collapse');
        setSubDetailsButton();
    });

    function toggleItems(item)
    {
        $(item).slideToggle();
    }
    function setSubDetailsButton() {
        $(".btn-open_details").html('<i class="fa fa-upload text-black" ></i>')
    }
    $(".box-categories").children('a').on('click', function() {
        $('.box-categories').children('a').addClass("category-unselected");
        $(this).removeClass("category-unselected");
        $(this).addClass("category-selected");
        $(this).css({"background-color" : MAIN_THEME_COLOR});
        $('.item').addClass("collapse");
        $("#a" + $(this).attr("id")).removeClass('collapse');
    });

    // showing category items ended


    function setBackElementsThisTheme() {
        try
        {
            var data = "{{$restaurant->back_theme_color_code}}";
            data = JSON.parse(data.replace(/&quot;/g,'"'));
            if(data["type"] == 1)
            {
                $("#app_main").css({
                    "background-image" : "url('"+data["value"]+"')" ,
                    "background-size" : "cover"
                });
            }
            else 
            {
                $("#app_main").css({
                    "background-color" : "#"+data["value"] ,
                    "background-size" : "cover"
                });
            }
        } 
        catch(err) 
        {  //We can also throw from try block and catch it here
            
        }


    }
</script>
@endsection