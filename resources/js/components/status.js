let currentDate = '';

Array.from(document.getElementsByClassName('status')).forEach(elem => {
   if (elem.dataset.date != currentDate) {
       currentDate = elem.dataset.date;
       const heading = document.createElement("h5");
       const textnode = document.createTextNode(currentDate);
       heading.classList.add("mt-4");
       heading.appendChild(textnode);
       elem.parentNode.insertBefore(heading, elem);
   }
});
