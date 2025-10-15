import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
  TrendingUp, 
  Target, 
  BarChart3, 
  FileText, 
  CheckCircle, 
  Clock,
  AlertTriangle,
  Users,
  Calendar,
  Download,
  RefreshCw
} from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, PieChart, Pie, Cell } from 'recharts';

export default function SakipDashboard({ dashboardData, period }) {
  const [selectedPeriod, setSelectedPeriod] = useState(period);
  const [isLoading, setIsLoading] = useState(false);
  const [performanceSummary, setPerformanceSummary] = useState(null);
  const [achievementTrends, setAchievementTrends] = useState([]);
  const [complianceStatus, setComplianceStatus] = useState(null);

  // Mock data for demonstration
  const mockDashboardData = {
    total_indicators: 25,
    active_targets: 20,
    submitted_data: 18,
    completed_assessments: 15,
    overall_achievement: 78.5,
    compliance_rate: 85.2,
    recent_activities: [
      {
        type: 'data_submission',
        description: 'Performance data submitted for Customer Satisfaction',
        user: 'John Doe',
        timestamp: '2024-01-15 10:30:00',
      },
      {
        type: 'assessment',
        description: 'Assessment completed for Financial Performance',
        user: 'Jane Smith',
        timestamp: '2024-01-14 14:20:00',
      },
      {
        type: 'target_update',
        description: 'Target updated for Service Quality',
        user: 'Mike Johnson',
        timestamp: '2024-01-13 09:15:00',
      },
    ],
    top_performers: [
      {
        indicator_name: 'Customer Satisfaction',
        achievement_percentage: 95.2,
        actual_value: 4.76,
        target_value: 5.0,
      },
      {
        indicator_name: 'Revenue Growth',
        achievement_percentage: 89.5,
        actual_value: 1790000,
        target_value: 2000000,
      },
      {
        indicator_name: 'Employee Productivity',
        achievement_percentage: 87.8,
        actual_value: 87.8,
        target_value: 100,
      },
    ],
    underperforming_indicators: [
      {
        indicator_name: 'Cost Reduction',
        achievement_percentage: 45.3,
        actual_value: 453000,
        target_value: 1000000,
      },
      {
        indicator_name: 'Process Efficiency',
        achievement_percentage: 52.1,
        actual_value: 52.1,
        target_value: 100,
      },
    ],
  };

  const mockAchievementTrends = [
    { period: '2024-01', actual: 75, target: 80 },
    { period: '2024-02', actual: 82, target: 80 },
    { period: '2024-03', actual: 78, target: 80 },
    { period: '2024-04', actual: 85, target: 80 },
    { period: '2024-05', actual: 79, target: 80 },
    { period: '2024-06', actual: 88, target: 80 },
  ];

  const mockComplianceData = [
    { name: 'Submitted', value: 18, color: '#10b981' },
    { name: 'Pending', value: 7, color: '#f59e0b' },
  ];

  const data = dashboardData || mockDashboardData;
  const trends = achievementTrends.length > 0 ? achievementTrends : mockAchievementTrends;
  const compliance = complianceStatus || mockComplianceData;

  const handleRefresh = async () => {
    setIsLoading(true);
    // Simulate API call
    setTimeout(() => {
      setIsLoading(false);
    }, 1000);
  };

  const handleExport = (type) => {
    // Simulate export functionality
    console.log(`Exporting ${type} data...`);
  };

  const getStatusColor = (percentage) => {
    if (percentage >= 90) return 'text-green-600';
    if (percentage >= 70) return 'text-yellow-600';
    return 'text-red-600';
  };

  const getStatusBadge = (percentage) => {
    if (percentage >= 90) return <Badge variant="success">Excellent</Badge>;
    if (percentage >= 70) return <Badge variant="warning">Good</Badge>;
    return <Badge variant="destructive">Needs Improvement</Badge>;
  };

  return (
    <>
      <Head title="SAKIP Dashboard" />
      
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">SAKIP Dashboard</h1>
            <p className="text-muted-foreground">
              Performance Management and Accountability System
            </p>
          </div>
          <div className="flex items-center gap-2">
            <select
              value={selectedPeriod}
              onChange={(e) => setSelectedPeriod(e.target.value)}
              className="px-3 py-2 border rounded-md bg-background"
            >
              <option value="2024">2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>
            </select>
            <Button
              onClick={handleRefresh}
              disabled={isLoading}
              variant="outline"
              size="sm"
            >
              <RefreshCw className={`h-4 w-4 mr-2 ${isLoading ? 'animate-spin' : ''}`} />
              Refresh
            </Button>
            <Button
              onClick={() => handleExport('summary')}
              variant="outline"
              size="sm"
            >
              <Download className="h-4 w-4 mr-2" />
              Export
            </Button>
          </div>
        </div>

        {/* Key Metrics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Indicators</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data.total_indicators}</div>
              <p className="text-xs text-muted-foreground">Performance indicators tracked</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active Targets</CardTitle>
              <Target className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data.active_targets}</div>
              <p className="text-xs text-muted-foreground">Targets set for current period</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Overall Achievement</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data.overall_achievement}%</div>
              <p className="text-xs text-muted-foreground">Average performance achievement</p>
              <Progress value={data.overall_achievement} className="mt-2" />
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Compliance Rate</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data.compliance_rate}%</div>
              <p className="text-xs text-muted-foreground">Data submission compliance</p>
              <Progress value={data.compliance_rate} className="mt-2" />
            </CardContent>
          </Card>
        </div>

        {/* Charts Row */}
        <div className="grid gap-4 md:grid-cols-2">
          {/* Achievement Trends Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Achievement Trends</CardTitle>
              <CardDescription>Performance achievement over time</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <LineChart data={trends}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="period" />
                  <YAxis />
                  <Tooltip />
                  <Line type="monotone" dataKey="actual" stroke="#2563eb" strokeWidth={2} name="Actual" />
                  <Line type="monotone" dataKey="target" stroke="#dc2626" strokeWidth={2} strokeDasharray="5 5" name="Target" />
                </LineChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          {/* Compliance Status Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Compliance Status</CardTitle>
              <CardDescription>Data submission status overview</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <PieChart>
                  <Pie
                    data={compliance}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                  >
                    {compliance.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </div>

        {/* Performance Tables */}
        <div className="grid gap-4 md:grid-cols-2">
          {/* Top Performers */}
          <Card>
            <CardHeader>
              <CardTitle>Top Performers</CardTitle>
              <CardDescription>Best performing indicators</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data.top_performers.map((item, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="space-y-1">
                      <p className="text-sm font-medium leading-none">{item.indicator_name}</p>
                      <p className="text-sm text-muted-foreground">
                        {item.actual_value} / {item.target_value}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className={`text-sm font-medium ${getStatusColor(item.achievement_percentage)}`}>
                        {item.achievement_percentage}%
                      </p>
                      {getStatusBadge(item.achievement_percentage)}
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Underperforming Indicators */}
          <Card>
            <CardHeader>
              <CardTitle>Needs Attention</CardTitle>
              <CardDescription>Indicators requiring improvement</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {data.underperforming_indicators.map((item, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="space-y-1">
                      <p className="text-sm font-medium leading-none">{item.indicator_name}</p>
                      <p className="text-sm text-muted-foreground">
                        {item.actual_value} / {item.target_value}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className={`text-sm font-medium ${getStatusColor(item.achievement_percentage)}`}>
                        {item.achievement_percentage}%
                      </p>
                      <Badge variant="destructive">Needs Improvement</Badge>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Recent Activities */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Activities</CardTitle>
            <CardDescription>Latest SAKIP system activities</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {data.recent_activities.map((activity, index) => (
                <div key={index} className="flex items-start space-x-4">
                  <div className="mt-1">
                    {activity.type === 'data_submission' && <FileText className="h-4 w-4 text-blue-600" />}
                    {activity.type === 'assessment' && <CheckCircle className="h-4 w-4 text-green-600" />}
                    {activity.type === 'target_update' && <Target className="h-4 w-4 text-orange-600" />}
                  </div>
                  <div className="flex-1 space-y-1">
                    <p className="text-sm font-medium">{activity.description}</p>
                    <p className="text-xs text-muted-foreground">
                      By {activity.user} • {new Date(activity.timestamp).toLocaleString()}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}