{% extends 'global/master.tpl' %}

{% block title %}Dashboard{% endblock %}

{% block content %}
    <div class="main">
        <h1 class="page-header">Dashboard <small>A quick overview of everything</small></h1>
        <h3>Reports <small>The five newest unsolved reports</small></h3>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>User reported</th>
                    <th>Reporter</th>
                    <th>Report time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Faggot exists really</td>
                    <td>Nippon Nick
                    </td>
                    <td>Japan Joe</td>
                    <td>1969-69-69 69:69:69 JST</td>
                </tr>
            </tbody>
        </table>
        <h3>Statistics <small>The site in numbers</small></h3>
    </div>
{% endblock %}
