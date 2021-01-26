	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="post-filters">
					{!! Theme::partial('usermenu-settings') !!}
				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-heading no-bg panel-settings">
					@include('flash::message')
						<h3 class="panel-title">
							{{ trans('common.earnings') }}
						</h3>
					</div>
					<div class="panel-body nopadding">
                        <div class="tab">
                            <button class="tablinks active" onclick="openCity(event, 'Overview')">
                                Overview
                            </button>
                            <button class="tablinks" onclick="openCity(event, 'Chargebacks')">
                                Chargebacks
                            </button>
                            <button class="tablinks" onclick="openCity(event, 'Statistics')">
                                Statistics
                            </button>
                        </div>
                        <div id="Overview" class="tabcontent">
                            <div class="panel-body">
                                <div class="table-responsive manage-table">
                                    <table class="table fans">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Gross</th>
                                                <th>Fee</th>
                                                <th>Net</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding: 5px">Oct. 27, 2020</td>
                                                <td style="padding: 5px">Recurring subscription from <a href="http://fansplatform.mjmdesign.co/username-here">[username-here]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Paid out" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                      <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                                    </svg>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px">Oct. 25, 2020</td>
                                                <td style="padding: 5px">Recurring subscription from <a href="http://fansplatform.mjmdesign.co/username-guy">[username-guy]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Pending" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-hourglass-split" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2h-7zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48V8.35zm1 0c0 .701.478 1.236 1.011 1.492A3.5 3.5 0 0 1 11.5 13s-.866-1.299-3-1.48V8.35z"/>
                                                </svg>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px">Oct. 12, 2020</td>
                                                <td style="padding: 5px">Recurring subscription from <a href="http://fansplatform.mjmdesign.co/username-girl">[username-girl]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Complete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z"/>
                                                    </svg>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="Chargebacks" class="tabcontent">
                            <div class="panel-body">
                                <div class="table-responsive manage-table">
                                    <table class="table fans">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Gross</th>
                                                <th>Fee</th>
                                                <th>Net</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding: 5px">Oct. 26, 2020</td>
                                                <td style="padding: 5px">Chargeback from <a href="http://fansplatform.mjmdesign.co/username-here">[username-here]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Paid out" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                      <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                                    </svg>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px">Oct. 25, 2020</td>
                                                <td style="padding: 5px">Refund from <a href="http://fansplatform.mjmdesign.co/username-guy">[username-guy]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Pending" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-hourglass-split" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2h-7zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48V8.35zm1 0c0 .701.478 1.236 1.011 1.492A3.5 3.5 0 0 1 11.5 13s-.866-1.299-3-1.48V8.35z"/>
                                                </svg>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px">Oct. 10, 2020</td>
                                                <td style="padding: 5px">Chargeback from <a href="http://fansplatform.mjmdesign.co/username-girl">[username-girl]</a></td>
                                                <td style="padding: 5px">$29.99</td>
                                                <td style="padding: 5px">$4.49</td>
                                                <td style="padding: 5px">$25.49</td>
                                                <td style="padding: 5px; text-align:center;">
                                                    <svg data-toggle="tooltip" data-original-title="Complete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z"/>
                                                    </svg>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="Statistics" class="tabcontent">
                            <div class="fans-form">
                                Analytics Graphs Placeholder
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
    </div>
        <script>
            $("#Overview").slideDown();

            function openCity(evt, cityName) {
                // Declare all variables
                var i, tabcontent, tablinks;

                // Get all elements with class="tabcontent" and hide them
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }

                // Get all elements with class="tablinks" and remove the class "active"
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }

                // Show the current tab, and add an "active" class to the button that opened the tab
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " active";
            }
        </script>