import $ from "jquery";

class Search {
  // 1. describe and initialize
  constructor() {
    this.addSearchHTML();
    this.openButton = $(".js-search-trigger");
    this.closeButton = $(".search-overlay__close");
    this.searchOverlay = $(".search-overlay");
    this.searchField = $("#search-term");
    this.resultsDiv = $("#search-overlay__results");
    this.events();
    this.isOverlayOpen = false;
    this.isSpinnerVisible = false;
    this.previousValue;
    this.typingTimer;
  }

  // 2. events
  events() {
    this.openButton.on("click", this.openOverlay.bind(this));
    this.closeButton.on("click", this.closeOverlay.bind(this));

    $(document).on("keydown", this.keyPressDispacher.bind(this));

    this.searchField.on("keyup", this.typingLogic.bind(this));
  }

  //3. Methods ( function or action )
  openOverlay() {
    this.searchOverlay.addClass("search-overlay--active");
    $("body").addClass("body-no-scroll");
    this.searchField.val("");
    setTimeout(() => {
      this.searchField.focus();
    }, 301);
    this.isOverlayOpen = true;
    return false;
  }

  closeOverlay() {
    this.searchOverlay.removeClass("search-overlay--active");
    $("body").removeClass("body-no-scroll");
    this.isOverlayOpen = false;
  }

  keyPressDispacher(event) {
    if (
      event.keyCode == 83 &&
      !this.isOverlayOpen &&
      !$("input, textarea").is(":focus")
    ) {
      this.openOverlay();
    }
    if (event.keyCode == 27 && this.isOverlayOpen) {
      this.closeOverlay();
    }
  }

  typingLogic() {
    if (this.searchField.val() !== this.previousValue) {
      clearTimeout(this.typingTimer);

      if (this.searchField.val()) {
        if (!this.isSpinnerVisible) {
          this.resultsDiv.html('<div class="spinner-loader"></div>');
          this.isSpinnerVisible = true;
        }
        this.typingTimer = setTimeout(this.getResults.bind(this), 750);
      } else {
        this.resultsDiv.html("");
        this.isSpinnerVisible = false;
      }
    }
    this.previousValue = this.searchField.val();
  }

  getResults() {
    $.getJSON(
      universityData.root_url +
        "/wp-json/university/v1/search?term=" +
        this.searchField.val(),
      (results) => {
        this.resultsDiv.html(`
         <div class="row">
            <div class="one-third">
               <h2 class="search-overlay__section-title">General information</h2>
               ${
                 results.generalInfo.length
                   ? '<ul class="link-list min-list">'
                   : "<p>No posts found</p>"
               }
                ${results.generalInfo
                  .map(
                    (item) =>
                      `<li><a href="${item.permalink}">${item.title}</a> ${
                        item.postType == "post" ? `by ${item.authorName}` : ""
                      }</li>`
                  )
                  .join("")}
                
                ${results.generalInfo.length ? "</ul>" : ""}      
            </div>

            <div class="one-third">
               <h2 class="search-overlay__section-title">Programs</h2>
               ${
                 results.programs.length
                   ? '<ul class="link-list min-list">'
                   : `<p>No programs found. <a href='${universityData.root_url}/programs'>View all programs</a></p>`
               }
                 ${results.programs
                   .map(
                     (item) =>
                       `<li><a href="${item.permalink}">${item.title}</a></li>`
                   )
                   .join("")}
               
                 ${results.programs.length ? "</ul>" : ""}   


               <h2 class="search-overlay__section-title">Professors</h2>
               ${
                 results.professors.length
                   ? '<ul class="professor-cards">'
                   : "<p>No professors found.</p>"
               }
                    ${results.professors
                      .map(
                        (item) =>
                          `
                           <li class="professor-card__list-item">
                              <a class="professor-card" href="${item.permalink}">
                                    <image class="professor-card__image" src="${item.image}">
                                    <span class="professor-card__name">${item.title}</span>
                              </a>
                           </li>                              
                          `
                      )
                      .join("")}
                  
                    ${results.professors.length ? "</ul>" : ""}  
            </div>

            <div class="one-third">
               <h2 class="search-overlay__section-title">Campuses</h2>
               ${
                 results.campuses.length
                   ? '<ul class="link-list min-list">'
                   : `<p>No campuses found. <a href='${universityData.root_url}/campuses'>View all Campuses</a></p>`
               }
                   ${results.campuses
                     .map(
                       (item) =>
                         `<li><a href="${item.permalink}">${item.title}</a></li>`
                     )
                     .join("")}
                 
                   ${results.campuses.length ? "</ul>" : ""}               
               <h2 class="search-overlay__section-title">Events</h2>
                  ${
                    results.events.length
                      ? ""
                      : `<p>No events found. <a href='${universityData.root_url}/events'>View all events</a></p>`
                  }
                  ${results.events
                    .map(
                      (item) =>
                        `
                        <div class="event-summary">
                           <a class="event-summary__date t-center" href="${item.permalink}">
                              <span class="event-summary__month">${item.month}</span>
                              <span class="event-summary__day">${item.day}</span>
                           </a>
                           <div class="event-summary__content">
                              <h5 class="event-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
                              <p>${item.excerpt}<a href="${item.permalink}" class="nu gray">Learn more</a></p>
                           </div>
                        </div>
                        `
                    )
                    .join("")} 
            </div>
         </div>
      `);
        this.isSpinnerVisible = false;
      }
    );

    //  // Delete later on
    //  $.when(
    //    $.getJSON(
    //      universityData.root_url +
    //        "/wp-json/wp/v2/posts?search=" +
    //        this.searchField.val()
    //    ),
    //    $.getJSON(
    //      universityData.root_url +
    //        "/wp-json/wp/v2/pages?search=" +
    //        this.searchField.val()
    //    )
    //  ).then(
    //    (posts, pages) => {
    //      var combinedResults = posts[0].concat(pages[0]);
    //      this.resultsDiv.html(`
    //      <h2 class="search-overlay__section-title">General info</h2>
    //      ${
    //        combinedResults.length
    //          ? '<ul class="link-list min-list">'
    //          : "<p>No posts found</p>"
    //      }
    //      ${combinedResults
    //        .map(
    //          (combinedResults) =>
    //            `<li><a href="${combinedResults.link}">${
    //              combinedResults.title.rendered
    //            }</a> ${
    //              combinedResults.type == "post"
    //                ? `by ${combinedResults.authorName}`
    //                : ""
    //            }</li>`
    //        )
    //        .join("")}

    //      ${combinedResults.length ? "</ul>" : ""}
    //    `);
    //      this.isSpinnerVisible = false;
    //    },
    //    () => {
    //      this.resultsDiv.html("Unexpected Error, please try again");
    //    }
    //  );
  }

  addSearchHTML() {
    $("body").append(`    
    <div class="search-overlay">
      <div class="search-overlay__top">
        <div class="container">
          <i class="fa fa-search search-overlay__icon" area-hidden="true"></i>
          <input type="text" class="search-term" placeholder="Search" id="search-term" autocomplete="off">
          <i class="fa fa-window-close search-overlay__close" area-hidden="true"></i>
        </div>
      </div>
      <div class="container">
        <div id=search-overlay__results></div>
      </div>
  </div>`);
  }
}

export default Search;
