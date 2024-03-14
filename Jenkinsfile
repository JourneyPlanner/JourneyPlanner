pipeline {
    agent any
    stages {
        stage('Build frontend') {
            steps {
                dir('frontend') {
                    script {
                        checkout scm
                        def frontend = docker.build("journeyplanner/frontend:latest")
                    }
                }
            }
        }
        stage('Build backend') {
            steps {
                dir('backend') {
                    script {
                        checkout scm
                        def backend = docker.build("journeyplanner/backend:latest")
                    }
                }
            }
        }
    }
}
