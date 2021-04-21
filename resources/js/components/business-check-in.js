let businessUserIcon = $("#business-user");
let businessBriefcaseIcon = $("#business-briefcase");
let businessBuildingIcon = $("#business-building");

let businessCheckInput = $("#business_check");
$(businessCheckInput).val("0");

let businessUser = $("#business-li-user");
let businessBriefcase = $("#business-li-briefcase");
let businessBuilding = $("#business-li-building");

$(businessUser).on("click", function (){
    $(businessUserIcon).removeClass("d-none");
    $(businessBriefcaseIcon).addClass("d-none");
    $(businessBuildingIcon).addClass("d-none");
    $(businessCheckInput).val("0");
})

$(businessBriefcase).on("click", function (){
    $(businessUserIcon).addClass("d-none");
    $(businessBriefcaseIcon).removeClass("d-none");
    $(businessBuildingIcon).addClass("d-none");
    $(businessCheckInput).val("1");
})

$(businessBuilding).on("click", function (){
    $(businessUserIcon).addClass("d-none");
    $(businessBriefcaseIcon).addClass("d-none");
    $(businessBuildingIcon).removeClass("d-none");
    $(businessCheckInput).val("2");
})