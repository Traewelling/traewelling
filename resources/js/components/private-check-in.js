let privacyLockIcon = $("#privacy-lock");
let privacyUnlockIcon = $("#privacy-unlock");
let privacyGlobeIcon = $("#privacy-globe");
let privacyCheckInput = $("privacy_check");

let privacyLock = $("#privacy-li-lock");
let privacyGlobe = $("#privacy-li-globe");
let privacyUnlock = $("#privacy-li-unlock");

$(privacyLock).on("click", function (){
    $(privacyLockIcon).removeClass("d-none");
    $(privacyUnlockIcon).addClass("d-none");
    $(privacyGlobeIcon).addClass("d-none");
    $(privacyCheckInput).val("1");
})

$(privacyGlobe).on("click", function (){
    $(privacyLockIcon).addClass("d-none");
    $(privacyUnlockIcon).addClass("d-none");
    $(privacyGlobeIcon).removeClass("d-none");
    $(privacyCheckInput).val("2");
})

$(privacyUnlock).on("click", function (){
    $(privacyLockIcon).addClass("d-none");
    $(privacyUnlockIcon).removeClass("d-none");
    $(privacyGlobeIcon).addClass("d-none");
    $(privacyCheckInput).val("3");
})