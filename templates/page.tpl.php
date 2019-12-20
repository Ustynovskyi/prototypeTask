<div  ng-app="testApp">
    <div ng-controller="testPage" id="page" style="width: 100vw; hight: 100vh">
        <img src="/images/1041px-Star_Wars_Logo.svg.png" style="width: 25vw;">
        <br/>
        <button type="button" ng-click="runTestTasks()" class="buttonYellow">Do. Or do not. There is no try</button>

        <div ng-if="taskTwoResult">
            <p class="task">What character appeared in most of the Star Wars films?</p>
            <p class="result">{{taskTwoResult.result}}</p>
        </div>
    </div>
</div>

<script type="text/javascript">


    var protoTest=angular.module('testApp',[]);
    protoTest.controller('testPage',  function($scope, $http) {
        $scope.runTestTasks=function(){


            $http.get("/getTaskTwo")
                .then(function(response) {
                    if(response.data.code=='1')
                    {
                        $scope.taskTwoResult={result:response.data.result};
                    }
                });
        }
    });


</script>