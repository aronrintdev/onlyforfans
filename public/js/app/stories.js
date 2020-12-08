$( document ).ready(function() {
  console.log('stories init');

  const username = g_php2jsVars.session.username;
  const url = `${username}/stories`;
  $.getJSON(url, function(response) {
    $('.supercrate-stories').html(response.html);
  });

});
