let keycloak;

const booksLoaded = function (books) {
    console.log(books);

    const table = $("#books tbody");

    $.each(books, function (index, book) {
        const row = $("<tr></tr>");

        row.append($("<td></td>").text(book.isbn));
        row.append($("<td></td>").text(book.name));

        row.append($("<td></td>").text(book.authors.join(", ")));

        row.appendTo(table);
    });


};

const url = "//books-heinlein.apps.cc-openshift.de/";
//var url = "http://localhost:8889/";


$(function() {
    keycloak = Keycloak({
        "url": "https://keycloak-heinlein.apps.cc-openshift.de/auth",
        "realm": "heinlein",
        "clientId": "book-frontend"
    });

    keycloak.init({ onLoad: 'login-required'}).success(function (authenticated) {
        if (!authenticated) {
            $("body:first").html("Authentication required!!");
            return;
        }

        $.ajax({
            url: url,
            dataType: "json",
            success: booksLoaded,
            error: function (a, b, c) { console.error(a); console.error(b); console.error(c); },
            headers: {
                "Authorization": "Bearer " + keycloak.token
            }
        });
    });


});