# Dicoding Product Engineer Technical Exercise

## Part 1: Technical Problem Solving

### Setup Instructions

1. **Clone the Repository**:

    - Open your terminal or command prompt.
    - Run the following command to clone the repository:
        ```sh
        git clone https://github.com/dicoding-dev/technical-excercise.git
        cd technical-excercise
        ```

2. **Set Up the Environment**:

    - Install the required dependencies using Composer:
        ```sh
        composer install
        ```
    - Copy the example environment configuration file and create a new `.env` file:
        ```sh
        cp .env.example .env
        ```
    - Generate a new application key:
        ```sh
        php artisan key:generate
        ```

3. **Run Database Migrations and Seeders**:
    - Run the database migrations and seeders to set up the database. If you encounter a memory limit error, try increasing the memory limit as follows:
        - Start with 4096M:
            ```sh
            php -d memory_limit=4096M artisan migrate --seed
            ```
        - If needed, you can increase it further to 12000M:
            ```sh
            php -d memory_limit=12000M artisan migrate --seed
            ```

### API Documentation

#### Drop Out Enrollments Command

-   **Command**: `enrollments:dropout`
-   **Description**: Drops out enrollments that meet specific criteria based on the deadline date.
-   **Usage**:
    ```sh
    php artisan enrollments:dropout
    ```

### Testing Instructions

1. **Benchmark Performance**:

    - Measure memory usage and execution time before and after optimizations.
    - Example benchmark command:
        ```sh
        php artisan enrollments:dropout
        ```

2. **Analyze Results**:
    - Compare the memory usage and execution time to identify improvements.
    - Document the results and include screenshots of the benchmarks.

By following these testing instructions, you can ensure that the code is working correctly and measure the performance improvements after optimizations.

## Part 2: Product Analysis

### Objective

Conduct a thorough analysis of Dicoding's platform and propose meaningful improvements to enhance the learning journey for Indonesian developers, focusing on Java Spring Boot and microservices courses for backend development in the banking and finance industry.

### Problem Analysis

#### Identified Pain Points

1. **Lack of Specialized Backend Development Courses**:

    - Dicoding offers Java courses primarily for Android development and backend development using JavaScript.
    - Modern banking and finance companies often use Java Spring Boot and microservices for secure, scalable, and maintainable systems, creating a gap in Dicoding's offerings.

2. **Limited Alignment with Industry Requirements**:
    - The absence of Java Spring Boot and microservices courses means learners are not fully equipped with the skills demanded by the banking and finance sectors.

### Solution Design

#### Proposed Feature Improvements

1. **Java Spring Boot Course**

    - **Beginner to Advanced Levels**: Comprehensive curriculum covering fundamental to advanced Java Spring Boot concepts.
    - **Real-world Projects**: Projects such as building secure REST APIs, integrating with banking systems, and handling transactions.
    - **Industry-Relevant**: Collaboration with industry professionals to ensure alignment with current banking and finance requirements.

2. **Microservices Architecture Course**
    - **Design Principles and Best Practices**: Covering principles of microservices architecture.
    - **Hands-on Projects**: Projects like creating a microservice for a payment gateway or user authentication system.
    - **Deployment and Monitoring**: Using tools like Docker and Kubernetes, and monitoring with Prometheus and Grafana.

#### Justification for Proposed Improvements

-   **High Demand in Banking and Finance**: As highlighted in the [Adeva article](https://adevait.com/java/java-in-banking), Java Spring Boot and microservices are essential for modern banking and finance applications.
-   **Enhanced Employability**: Equipping learners with these skills will make them more attractive to employers in the banking and finance sector, increasing their job placement rates.
-   **Industry Trends**: Modern banking and finance companies adopt microservices architecture to build scalable and maintainable systems, making these skills essential for developers.

### Implementation Strategy

1. **Collaborate with Industry Experts**

    - Partner with professionals from banking and finance sectors to design the curriculum.
    - Conduct workshops and guest lectures to provide real-world insights.

2. **Develop Comprehensive Course Material**

    - Create detailed tutorials, documentation, and video content.
    - Include practical examples and case studies relevant to the banking and finance industry.

3. **Offer Certifications**

    - Provide certifications upon course completion to validate skills.
    - Align certifications with industry standards to enhance job prospects.

4. **Incorporate Interactive Elements**
    - Use quizzes, coding challenges, and peer reviews to engage learners.
    - Implement project-based learning to provide hands-on experience.

### Metrics for Measuring Success

1. **Enrollment Numbers**

    - Track the number of students enrolling in the new courses.
    - Measure growth in enrollment over time.

2. **Completion Rates**

    - Monitor the percentage of students completing the courses.
    - Identify any drop-off points and address potential issues.

3. **Job Placement Rates**

    - Measure the number of students securing jobs in banking and finance after completing the courses.
    - Track the types of roles and companies where students are getting hired.

4. **Student Feedback**
    - Gather feedback from students to continually improve the course content.
    - Use surveys and reviews to assess student satisfaction and learning outcomes.

### Conclusion

By addressing the gap in Java Spring Boot and microservices architecture courses, Dicoding can better serve Indonesian developers seeking careers in the banking and finance industry. These improvements will enhance the platform's value and attract more learners looking to acquire these in-demand skills.
