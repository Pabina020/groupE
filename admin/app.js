// app.js - Shared data management for all pages
class RentalAdmin {
  constructor() {
    this.initData();
  }

  initData() {
    if (!localStorage.getItem('rentalAdminData')) {
      const initialData = {
        users: [
          {
            id: 1,
            name: "Alice Smith",
            email: "alice@example.com",
            phone: "+1 234-567-8901",
            joinDate: "2024-01-15",
            status: "Active",
            role: "Tenant"
          },
          {
            id: 2,
            name: "Bob Johnson",
            email: "bob@example.com",
            phone: "+1 234-567-8902",
            joinDate: "2024-02-01",
            status: "Active",
            role: "Owner"
          },
          {
            id: 3,
            name: "Carol White",
            email: "carol@example.com",
            phone: "+1 234-567-8903",
            joinDate: "2024-02-15",
            status: "Inactive",
            role: "Tenant"
          }
        ],
        properties: [
          {
            id: 1,
            name: "Luxury Villa",
            address: "123 Ocean Drive",
            type: "Villa",
            bedrooms: 4,
            price: 350,
            status: "Available",
            billingProof: "uploads/sample_invoice1.pdf"
          },
          {
            id: 2,
            name: "Downtown Apartment",
            address: "456 City Center",
            type: "Apartment",
            bedrooms: 2,
            price: 200,
            status: "Occupied",
            billingProof: ""
          },
          {
            id: 3,
            name: "Beach House",
            address: "789 Coastal Road",
            type: "House",
            bedrooms: 3,
            price: 280,
            status: "Available",
            billingProof: ""
          }
        ],
        bookings: [
          {
            id: "B001",
            propertyId: 1,
            tenantId: 1,
            checkIn: "2024-03-15",
            checkOut: "2024-03-20",
            total: 1750,
            status: "Confirmed"
          },
          {
            id: "B002",
            propertyId: 2,
            tenantId: 2,
            checkIn: "2024-03-18",
            checkOut: "2024-03-22",
            total: 800,
            status: "Pending"
          },
          {
            id: "B003",
            propertyId: 3,
            tenantId: 3,
            checkIn: "2024-03-25",
            checkOut: "2024-03-30",
            total: 1400,
            status: "Confirmed"
          }
        ],
        payments: [
          {
            bookingId: "B001",
            tenantId: 1,
            propertyId: 1,
            amount: 1750,
            status: "Completed",
            date: "2024-03-15"
          },
          {
            bookingId: "B002",
            tenantId: 2,
            propertyId: 2,
            amount: 800,
            status: "Pending",
            date: "2024-03-18"
          },
          {
            bookingId: "B003",
            tenantId: 3,
            propertyId: 3,
            amount: 1400,
            status: "Completed",
            date: "2024-03-25"
          }
        ]
      };
      localStorage.setItem('rentalAdminData', JSON.stringify(initialData));
    }
  }

  getData() {
    return JSON.parse(localStorage.getItem('rentalAdminData'));
  }

  saveData(data) {
    localStorage.setItem('rentalAdminData', JSON.stringify(data));
  }

  // User methods
  getUsers() {
    return this.getData().users;
  }

  addUser(user) {
    const data = this.getData();
    const newId = data.users.length > 0 ? Math.max(...data.users.map(u => u.id)) + 1 : 1;
    user.id = newId;
    data.users.push(user);
    this.saveData(data);
    return user;
  }

  // Property methods
  getProperties() {
    return this.getData().properties;
  }

  addProperty(property) {
    const data = this.getData();
    const newId = data.properties.length > 0 ? Math.max(...data.properties.map(p => p.id)) + 1 : 1;
    property.id = newId;
    data.properties.push(property);
    this.saveData(data);
    return property;
  }

  // Booking methods
  getBookings() {
    return this.getData().bookings;
  }

  // Payment methods
  getPayments() {
    return this.getData().payments;
  }

  // Helper methods
  getUserById(id) {
    return this.getUsers().find(user => user.id === id);
  }

  getPropertyById(id) {
    return this.getProperties().find(property => property.id === id);
  }
}

// Initialize the app
const rentalAdmin = new RentalAdmin();