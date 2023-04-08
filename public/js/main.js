// Tabs
function changeActiveTab(event,tabID){
    let element = event.target;
    while(element.nodeName !== "A"){
        element = element.parentNode;
    }
    
    ulElement = element.parentNode.parentNode;
    aElements = ulElement.querySelectorAll("li > a");
    iElements = ulElement.querySelectorAll("li > a > i");
    tabContents = document.getElementById("tabs").querySelectorAll(".tab-content > div");
    
    for(let i = 0 ; i < aElements.length; i++) {
        aElements[i].classList.remove("text-white");
        aElements[i].classList.remove("bg-blue-500");

        aElements[i].classList.add("text-blue-500");
        aElements[i].classList.add("bg-white");
        aElements[i].classList.add("dark:text-gray-300");
        aElements[i].classList.add("dark:bg-gray-800");
        
        tabContents[i].classList.add("hidden");
        tabContents[i].classList.remove("block");
    }
    element.classList.remove("text-blue-500");
    element.classList.remove("bg-white");
    element.classList.remove("dark:text-gray-300");
    element.classList.remove("dark:bg-gray-800");
    element.classList.add("text-white");
    element.classList.add("bg-blue-500");
    document.getElementById(tabID).classList.remove("hidden");
    document.getElementById(tabID).classList.add("block");
}

$(window).on('load', function(){
    // $('#loading').hide();
    if(document.getElementById('tabs')) {
        let ulElement = document.getElementById('tabs').getElementsByTagName('div')[0].getElementsByTagName('ul')[0];
        let hash = window.location.hash;
        let tabID = "tab-"+hash.substring(1);

        if(hash != '' && document.getElementById(tabID)) {
            aElements = ulElement.querySelectorAll("li > a");
            tabContents = document.getElementById("tabs").querySelectorAll(".tab-content > div");
            
            for(let i = 0 ; i < aElements.length; i++) {
                aElements[i].classList.remove("text-white");
                aElements[i].classList.remove("bg-blue-500");
                
                aElements[i].classList.add("dark:text-gray-300");
                aElements[i].classList.add("dark:bg-gray-800");
                aElements[i].classList.add("text-blue-500");
                aElements[i].classList.add("bg-white");

                tabContents[i].classList.add("hidden");
                tabContents[i].classList.remove("block");
            }
        
            element = document.getElementById(hash);
            
            element.classList.remove("text-blue-500");
            element.classList.remove("bg-white");
            element.classList.remove("dark:text-gray-300");
            element.classList.remove("dark:bg-gray-800");

            element.classList.add("text-white");
            element.classList.add("bg-blue-500");
            document.getElementById(tabID).classList.remove("hidden");
            document.getElementById(tabID).classList.add("block");
        }
    }
});