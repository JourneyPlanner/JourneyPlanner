pipeline {
    agent any
    environment {
        FRONTEND_IMAGE = 'journeyplanner/frontend:latest'
        BACKEND_IMAGE = 'journeyplanner/backend:latest'
        I18N_IMAGE = 'journeyplanner/i18n:latest'
        FRONTEND_INFISICAL_API_URL = credentials('frontend_infisical_api_url')
        FRONTEND_INFISICAL_TOKEN = credentials('frontend_infisical_token')
        }
    stages {
        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    script {
                        docker.build(env.FRONTEND_IMAGE, '--build-arg INFISICAL_API_URL=${FRONTEND_INFISICAL_API_URL} --build-arg INFISICAL_TOKEN=${FRONTEND_INFISICAL_TOKEN} .')
                    }
                }
            }
        }
        stage('Check Frontend Code') {
            steps {
                dir('frontend') {
                    script {
                        sh 'npm install'
                        sh 'npx prettier . --check'
                        sh 'npx eslint .'
                    }
                }
            }
        }
        stage('Build Backend') {
            steps {
                dir('backend') {
                    script {
                        docker.build(env.BACKEND_IMAGE)
                    }
                }
            }
        }
        stage('Check Backend Code') {
            steps {
                dir('backend') {
                    script {
                        sh 'npm install'
                        sh 'npx prettier . --check'
                    }
                }
            }
        }
        stage('Build i18n Image') {
            steps {
                dir('docker/i18n') {
                    script {
                        docker.build(env.I18N_IMAGE)
                    }
                }
            }
        }
    }
}
