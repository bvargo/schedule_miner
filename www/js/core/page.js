$(document).ready(function()
{
   $("#search_link").click(function(e)
   {
      e.preventDefault();
      if($("#search_open_close").is(":hidden"))
      {
         $("#search_bar").animate({marginTop:"0px"}, 300);
         $("#search_field").focus();
      }
      else
      {
         $("#search_bar").animate({marginTop:"-33px"}, 300);
      }
      $("#search_open_close").toggle();
   });

   $("#search_bar").mouseup(function()
   {
      return false;
   });

   $(document).mouseup(function(e)
   {
      if($(e.target).attr("id") != "search_link")
      {
         if($("#search_open_close").is(":visible"))
         {
            $("#search_bar").animate({marginTop:"-33px"}, 500);
            $("#search_open_close").hide();
         }
      }
   });
});
